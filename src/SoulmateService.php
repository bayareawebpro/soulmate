<?php declare(strict_types=1);

namespace BayAreaWebPro\Soulmate;

use BayAreaWebPro\Soulmate\Chat\Conversation;
use BayAreaWebPro\Soulmate\Chat\Message;
use BayAreaWebPro\Soulmate\Chat\Response;
use BayAreaWebPro\Soulmate\Enums\Endpoint;
use BayAreaWebPro\Soulmate\Enums\FinishReason;
use BayAreaWebPro\Soulmate\Enums\Role;
use BayAreaWebPro\Soulmate\Exceptions\UnexpectedOutput;
use BayAreaWebPro\Soulmate\Providers\OllamaProvider;
use BayAreaWebPro\Soulmate\Providers\Provider;
use BayAreaWebPro\Soulmate\Tools\Tool;
use BayAreaWebPro\Soulmate\Tools\ToolMessage;
use Illuminate\Container\Attributes\Config;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class SoulmateService
{
    protected string|null $system = null;

    public function __construct(
        public Conversation   $conversation,
        protected Collection    $tools,
        protected Provider    $provider = new OllamaProvider,
        #[Config('soulmate.secret', null)] #[\SensitiveParameter]
        protected string|null $apiToken = null,
    )
    {
    }

    protected function getClient(): PendingRequest
    {
        $client = Http::asJson()
            ->baseUrl($this->provider::BASE_URL)
            ->connectTimeout(10)
            ->timeout(120);

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

    public function tool(string $class, string $method): self
    {
        $this->tools->push(new Tool($class, $method));
        return $this;
    }

    public function system(string $prompt): self
    {
        $this->system = $prompt;
        return $this;
    }

    protected function getToolConfig(): Collection
    {
        return $this->tools->map(fn(Tool $tool) => $tool->toArray());
    }

    public function chatCompletion(Collection $messages, bool $usesTools = true): Response
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

        $response = $this->getClient()
            ->withBody($query->toJson())
            ->post(Endpoint::CHAT_COMPLETIONS->value);

        if($response->clientError() || $response->serverError()){
            return new Response(
                content: Str::of($response->toException()->getMessage())->trim(),
                finishReason: FinishReason::CONTENT_FILTER,
            );
        }

        $chatResponse = new Response(
            content: Str::of($response->json('choices.0.message.content'))->trim(),
            finishReason: FinishReason::from($response->json('choices.0.finish_reason')),
            toolCalls: $response->collect('choices.0.message.tool_calls'),
        );

        return match ($chatResponse->finishReason) {
            FinishReason::LENGTH => throw new \Exception('Conversation exceeded limits.'),
            FinishReason::CONTENT_FILTER => throw new \Exception('Content filtered due to policy violations.'),
            default => $chatResponse,
        };
    }

    public function chat(array $messages): Message
    {
        // Store Messages and pass though the full chat history.
        $chatResponse = $this->chatCompletion(
            messages: $this->conversation->messages($messages)->toCollection(),
        );

        if ($chatResponse->finishReason === FinishReason::TOOL_CALLS && $chatResponse->toolCalls->count()) {

            // Store tool call message.
            $this->conversation->message(new Message(
                role: Role::ASSISTANT,
                tool_calls: $chatResponse->toolCalls->toArray()
            ));

            // Call tools and get context.
            $toolContext = $this->provideToolContext($chatResponse->toolCalls);

            // Append context to messages, and complete the assistant tool call response.
            $chatResponse = $this->chatCompletion(
                messages: $this->conversation->messages($toolContext)->toCollection(),
            );
        }

        if ($chatResponse->finishReason === FinishReason::STOP) {
            if ($chatResponse->content->isEmpty()) {
                throw new UnexpectedOutput('The conversation ended unexpectedly.');
            }
            if ($chatResponse->content->contains('function')) {
                throw new UnexpectedOutput('Invalid tool (function) call.');
            }
        }

        $this->conversation->message(
            $message = new Message(Role::ASSISTANT, $chatResponse->content->toString())
        );

        return $message;

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

    protected function provideToolContext(Collection $toolCalls): Collection
    {
        return $toolCalls->map(function (array $toolCall) {

            $functionId = Arr::get($toolCall, 'id');
            $method = Arr::get($toolCall, 'function.name');
            $arguments = Arr::get($toolCall, 'function.arguments');

            $tool = $this->tools->where('method', $method)->first();

            if (!$tool) {
                return new ToolMessage($functionId, [
                    'response' => "Tool Call Function ($method) does not exist.",
                    'status'   => 'error'
                ]);
            }

            try {
                if (is_string($arguments) && json_validate($arguments)) {
                    $arguments = json_decode($arguments, true);
                }

                    $rules = Collection::make(Arr::get($tool->toArray(), 'function.parameters.properties'))
                        ->map(fn(array $param) => ['required', $param['type']])
                        ->toArray();

                    $validator = Validator::make($arguments, $rules);

                    if ($validator->fails()) {

                        $errors = Collection::make($validator->errors()->toArray())
                            ->map(fn(array $errors) => Arr::first($errors))
                            ->toArray();

                        return new ToolMessage($functionId, [
                            'response' => "Invalid Tool Call Parameters",
                            'errors'   => $errors,
                            'status'   => 'error',
                        ]);
                    }

                return new ToolMessage($functionId, [
                    'response' => $tool->execute($arguments),
                    'status'   => 'success'
                ]);

            } catch (\Throwable $exception) {

                return new ToolMessage($functionId, [
                    'response'  => "Function could not be executed. Insufficient arguments.",
                    'errors' => [$exception->getMessage()],
                    'status'    => 'error'
                ]);
            }
        });
    }
}
