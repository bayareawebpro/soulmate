<?php declare(strict_types=1);

namespace BayAreaWebPro\Soulmate\Exceptions;

class UnexpectedOutput extends \RuntimeException
{
    protected $message = 'Unexpected output formatting was encountered.';
}
