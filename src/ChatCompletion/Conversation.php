<?php declare(strict_types=1);

namespace BayAreaWebPro\Soulmate\ChatCompletion;

use Illuminate\Contracts\Session\Session;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;

class Conversation implements Arrayable
{
    public function __construct(protected Session $session)
    {
        // Inject session storage.
    }

    public function message(Arrayable $message): self
    {
        $this->session->push('chat.conversation', $message->toArray());
        return $this;
    }

    public function messages(Collection|array $messages): self
    {
        foreach ($messages as $message) {
            $this->message($message);
        }
        return $this;
    }

    public function end(): self
    {
        $this->session->forget('chat');
        return $this;
    }

    public function ended(): bool
    {
        return $this->session->has('chat');
    }

    public function total(): int
    {
        return $this->toCollection()->count();
    }

    public function clear(): self
    {
        $this->session->forget('chat');
        return $this;
    }

    public function toCollection(): Collection
    {
        return Collection::make($this->toArray());
    }

    public function toArray(): array
    {
        return $this->session->get('chat.conversation', []);
    }
}
