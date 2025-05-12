<?php declare(strict_types=1);

namespace BayAreaWebPro\Soulmate\Exceptions;

class MissingToolRegistration extends \InvalidArgumentException
{
    protected $message = 'Tool is not registered.';
}
