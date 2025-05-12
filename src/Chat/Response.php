<?php declare(strict_types=1);

namespace BayAreaWebPro\Soulmate\Chat;

use BayAreaWebPro\Soulmate\Enums\FinishReason;
use Illuminate\Support\Collection;
use Illuminate\Support\Stringable;
use Illuminate\Support\Traits\Dumpable;

class Response
{

    use Dumpable;
    public function __construct(
        public Stringable   $content,
        public FinishReason $finishReason,
        public Collection   $toolCalls = new Collection,
    )
    {

    }
}
