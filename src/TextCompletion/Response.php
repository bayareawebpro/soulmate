<?php declare(strict_types=1);

namespace BayAreaWebPro\Soulmate\TextCompletion;

use BayAreaWebPro\Soulmate\Enums\FinishReason;
use Illuminate\Support\Stringable;
use Illuminate\Support\Traits\Dumpable;

class Response
{
    use Dumpable;

    public function __construct(
        public Stringable   $content,
        public FinishReason $finishReason,
    )
    {
        //
    }
}
