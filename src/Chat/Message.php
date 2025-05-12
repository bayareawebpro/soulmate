<?php declare(strict_types=1);

namespace BayAreaWebPro\Soulmate\Chat;

use BayAreaWebPro\Soulmate\Enums\Role;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\Dumpable;

class Message implements Arrayable
{
    use Dumpable;

    public function __construct(
        public Role    $role,
        public ?string $content = null,
        public array   $tool_calls = [],
        public ?string $uuid = null,
    )
    {

    }

    public function toArray(): array
    {
        $data = [
            'uuid' => $this->uuid ??= Str::uuid()->toString(),
            'role' => $this->role->value,
            'content' => $this->content
        ];
        if (!empty($this->tool_calls)) {
            $data['tool_calls'] = $this->tool_calls;
        }
        return $data;
    }
}
