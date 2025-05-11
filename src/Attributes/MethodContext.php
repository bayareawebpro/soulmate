<?php declare(strict_types=1);

namespace BayAreaWebPro\Soulmate\Attributes;

use Attribute;

#[Attribute]
class MethodContext
{
    public function __construct(public string $value)
    {
    }
}
