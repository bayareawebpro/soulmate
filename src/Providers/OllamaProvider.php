<?php declare(strict_types=1);

namespace BayAreaWebPro\Soulmate\Providers;

class OllamaProvider implements Provider
{
    const string BASE_URL = 'http://localhost:11434/v1';
    const string MODEL = 'llama3.1:latest';

    const array OPTIONS = [
        'temperature'     => 0.2,
        'max_new_tokens'  => 120,
        'response_format' => 'none',
        //'frequency_penalty' => -1,
    ];

}
