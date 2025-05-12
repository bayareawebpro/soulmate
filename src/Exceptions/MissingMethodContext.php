<?php declare(strict_types=1);

namespace BayAreaWebPro\Soulmate\Exceptions;

class MissingMethodContext extends \InvalidArgumentException
{
    protected $message = 'Tool method missing context.';
}
