<?php declare(strict_types=1);

namespace BayAreaWebPro\Soulmate\ChatCompletion;

use BayAreaWebPro\Soulmate\Enums\FinishReason;
use Illuminate\Support\Collection;
use Illuminate\Support\Fluent;
use Illuminate\Support\Stringable;
use Illuminate\Support\Traits\Dumpable;

class Response extends Fluent
{
    use Dumpable;

    public function __construct(
        public Stringable   $content,
        public FinishReason $finishReason,
        public Collection   $toolCalls = new Collection,
    )
    {
        //
    }
}
