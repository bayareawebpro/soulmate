<?php declare(strict_types=1);

namespace BayAreaWebPro\Soulmate\Exceptions;

class HttpApiException extends \RuntimeException
{
    protected $message = 'HTTP network failed to send request.';
}
