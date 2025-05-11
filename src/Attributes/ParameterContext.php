<?php declare(strict_types=1);

namespace BayAreaWebPro\Soulmate\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_PARAMETER | Attribute::IS_REPEATABLE)]
class ParameterContext
{
    public function __construct(public string $name, public string $value)
    {
    }
}
