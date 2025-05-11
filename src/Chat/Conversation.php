<?php declare(strict_types=1);

namespace BayAreaWebPro\Soulmate\Chat;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Session\Store;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;

class Conversation implements Arrayable
{
    public function __construct(protected Store $session)
    {
        //
    }

    public static function make(): self
    {
        return App::make(static::class);
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
