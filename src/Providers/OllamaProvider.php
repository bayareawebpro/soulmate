<?php declare(strict_types=1);

namespace BayAreaWebPro\Soulmate\Providers;

use Illuminate\Container\Attributes\Config;
use SensitiveParameter;

class OllamaProvider implements Provider
{
    const string BASE_URL = 'http://127.0.0.1:11434/v1';
    const string MODEL = 'llama3.1:latest';
    const array OPTIONS = [
        'temperature'     => 0.5,
        'response_format' => 'none',
    ];
}
