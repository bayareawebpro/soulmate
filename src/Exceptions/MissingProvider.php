<?php declare(strict_types=1);

namespace BayAreaWebPro\Soulmate\Exceptions;

class MissingProvider extends \InvalidArgumentException
{
    protected $message = 'No API provider has been set.';
}
