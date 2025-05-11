<?php declare(strict_types=1);

namespace BayAreaWebPro\Soulmate\Exceptions;

class MissingParameterContext extends \InvalidArgumentException
{
    protected $message = 'Tool method parameter missing context.';
}
