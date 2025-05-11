<?php declare(strict_types=1);

namespace BayAreaWebPro\Soulmate\Tools;

use BayAreaWebPro\Soulmate\Enums\Role;
use Illuminate\Contracts\Support\Arrayable;

class ToolMessage implements Arrayable
{

    public function __construct(
        public string $functionId,
        public array  $data,
    )
    {

    }

    public function toArray(): array
    {
        return [
            'tool_call_id' => $this->functionId,
            'role'         => Role::TOOL->value,
            'content'      => json_encode($this->data),
        ];
    }
}
