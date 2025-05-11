<?php declare(strict_types=1);

namespace BayAreaWebPro\Soulmate\Exceptions;

class MissingContext extends \InvalidArgumentException
{
    protected $message = 'Tool method missing context.';
}
