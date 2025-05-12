<?php declare(strict_types=1);

namespace BayAreaWebPro\Soulmate\Exceptions;

class InvalidParameterType extends \InvalidArgumentException
{
    protected $message = 'Tool method parameter must be built-in type (int, float, double, or string).';
}
