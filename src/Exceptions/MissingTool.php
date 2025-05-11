<?php declare(strict_types=1);

namespace BayAreaWebPro\Soulmate\Exceptions;

class MissingTool extends \InvalidArgumentException
{
    protected $message = 'Tool missing or unavailable.';
}
