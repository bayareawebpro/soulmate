<?php declare(strict_types=1);

namespace BayAreaWebPro\Soulmate\Traits;

class Dumpable
{
    public function dump(): self
    {
        dump($this);
        return $this;
    }
}
