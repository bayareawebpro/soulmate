<?php declare(strict_types=1);

namespace BayAreaWebPro\Soulmate;

use BayAreaWebPro\Soulmate\Chat\Conversation;
use BayAreaWebPro\Soulmate\Chat\Message;
use BayAreaWebPro\Soulmate\Chat\Response;
use BayAreaWebPro\Soulmate\Enums\Endpoint;
use BayAreaWebPro\Soulmate\Enums\FinishReason;
use BayAreaWebPro\Soulmate\Enums\Role;
use BayAreaWebPro\Soulmate\Exceptions\HttpApiException;
use BayAreaWebPro\Soulmate\Exceptions\UnexpectedOutput;
use BayAreaWebPro\Soulmate\Providers\OllamaProvider;
use BayAreaWebPro\Soulmate\Providers\Provider;
use BayAreaWebPro\Soulmate\Tools\Tool;
use BayAreaWebPro\Soulmate\Tools\ToolMessage;
use Illuminate\Container\Attributes\Config;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response as ClientResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;


class SoulmateService
{
    protected int $httpTimeout = 300;
    protected int $httpConnectTimeout = 10;
    protected string|null $system = null;

    public function __construct(
        public Conversation     $conversation,
        protected Collection    $tools,
        protected Provider      $provider = new OllamaProvider,
        #[Config('soulmate.secret', null)] #[\SensitiveParameter]
        protected string|null   $apiToken = null,
    )
    {
    }

    protected function getClient(): PendingRequest
    {
        $client = Http::asJson()
            ->acceptJson()
            ->baseUrl($this->provider::BASE_URL)
            ->connectTimeout($this->httpConnectTimeout)
            ->timeout($this->httpTimeout);

        if ($this->apiToken) {
            $client->withToken($this->apiToken);
        }

        return $client;
    }

    public function use(Provider $provider): self
    {
        $this->provider = $provider;
        return $this;
    }

    public function timeout(int $timeout): self
    {
        $this->httpTimeout = $timeout;
        return $this;
    }

    public function connectTimeout(int $timeout): self
    {
        $this->httpConnectTimeout = $timeout;
        return $this;
    }

    public function system(string $prompt): self
    {
        $this->system = $prompt;
        return $this;
    }

    public function tool(string $class, string $method): self
    {
        $this->tools->push(new Tool($class, $method));
        return $this;
    }

    protected function getToolConfig(): Collection
    {
        return $this->tools->map(fn(Tool $tool) => $tool->toArray());
    }

    public function embedding(string $content): Response
    {
        // Prepare API Query.
        $query = Collection::make([
            'model'    => $this->provider::MODEL,
            'options'  => $this->provider::OPTIONS,
            'prompt'   => $content,
            'stream'   => false,
        ]);

        return $this->handleResponse(
            $this
                ->getClient()
                ->withBody($query->toJson())
                ->post(Endpoint::EMBEDDINGS->value)
        );
    }

    public function completion(string $content): Response
    {
        $prompt = Collection::make([$this->system, $content])->filter();

        // Prepare API Query.
        $query = Collection::make([
            'model'    => $this->provider::MODEL,
            'options'  => $this->provider::OPTIONS,
            'prompt'   => $prompt->join("\r\n"),
            'stream'   => false,
        ]);

        return $this->handleResponse(
            $this
                ->getClient()
                ->withBody($query->toJson())
                ->post(Endpoint::COMPLETIONS->value)
        );
    }


    protected function chatCompletion(Collection $messages, bool $usesTools = true): Response
    {
        // Remove internal properties to reduce API request payload.
        $messages = $this->removeNonApiProperties($messages);

        // Prepend System Prompt.
        if ($this->system) {
            $messages->prepend((new Message(Role::SYSTEM, $this->system))->toArray());
        }

        // Prepare API Query.
        $query = Collection::make([
            'model'    => $this->provider::MODEL,
            'options'  => $this->provider::OPTIONS,
            'messages' => $messages->toArray(),
            'stream'   => false,
        ]);

        if ($usesTools) {
            $query->put('tools', $this->getToolConfig()->toArray());
        }

        return $this->handleResponse(
            $this
                ->getClient()
                ->withBody($query->toJson())
                ->post(Endpoint::CHAT_COMPLETIONS->value)
        );
    }

    public function chat(array $messages): Message
    {
        // Store Messages and pass though the full chat history.
        $response = $this->chatCompletion(
            messages: $this->conversation->messages($messages)->toCollection(),
        );

        if ($response->finishReason === FinishReason::TOOL_CALLS && $response->toolCalls->count()) {

            // Store tool call message.
            $this->conversation->message(new Message(
                role: Role::ASSISTANT,
                tool_calls: $response->toolCalls->toArray()
            ));

            // Call tools and get context.
            $toolContext = $this->generateToolContext($response->toolCalls);

            // Append context to messages, and complete the assistant tool call response.
            $response = $this->chatCompletion(
                messages: $this->conversation->messages($toolContext)->toCollection(),
            );
        }

        if ($response->finishReason === FinishReason::STOP) {
            if ($response->content->isEmpty()) {
                throw new UnexpectedOutput('LLM output was unexpectedly empty.');
            }
        }

        $this->conversation->message(
            $message = new Message(Role::ASSISTANT, $response->content->toString())
        );

        return $message;

    }

    protected function generateToolContext(Collection $toolCalls): Collection
    {
        return $toolCalls->map(function (array $toolCall) {

            $functionId = Arr::get($toolCall, 'id');
            $methodName = Arr::get($toolCall, 'function.name');
            $arguments  = Arr::get($toolCall, 'function.arguments');

            $tool = $this->tools->where('method', $methodName)->first();

            if (is_null($tool)) {
                return new ToolMessage($functionId, [
                    'response' => "Tool Call Function ($methodName) does not exist.",
                    'status'   => 'error'
                ]);
            }

            try {
                if (is_string($arguments) && json_validate($arguments)) {
                    $arguments = json_decode($arguments, true);
                }

                return new ToolMessage($functionId, [
                    'response' => $tool->execute($arguments),
                    'status'   => 'success'
                ]);

            } catch (\Throwable $exception) {

                return new ToolMessage($functionId, [
                    'response'  => "Function ($methodName) could not be executed. Invalid arguments.",
                    'errors'    => [$exception->getMessage()],
                    'status'    => 'error'
                ]);
            }
        });
    }

    protected function handleResponse(ClientResponse $response): Response
    {
        if($error = $response->json('error')){
            throw new HttpApiException($error);
        }

        if($response->failed()){
            throw ($response->toException() ?? new HttpApiException);
        }

        $finishReason = FinishReason::tryFrom($response->json('choices.0.finish_reason')) ?? FinishReason::STOP;

        if($completion = $response->json('choices.0.text')){
            return new Response(
                content: Str::of($completion),
                finishReason: $finishReason,
            );
        }

        return new Response(
            content: Str::of($response->json('choices.0.message.content')),
            finishReason: $finishReason,
            toolCalls: $response->collect('choices.0.message.tool_calls'),
        );
    }

    protected function removeNonApiProperties(Collection $messages): Collection
    {
        return $messages->map(function (Message|array $message) {
            if ($message instanceof Message) {
                $message = $message->toArray();
            }
            return Arr::except($message, 'uuid');
        });
    }
}
