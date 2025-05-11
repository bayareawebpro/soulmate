<?php declare(strict_types=1);

namespace BayAreaWebPro\Soulmate\Providers;

class DeepInfraProvider implements Provider
{
    const string BASE_URL = 'https://api.deepinfra.com/v1/openai';

    //const string MODEL = 'meta-llama/Meta-Llama-3.1-8B-Instruct';
    //const string MODEL = 'meta-llama/Meta-Llama-3.1-70B-Instruct';
    const string MODEL = 'meta-llama/Meta-Llama-3.1-70B-Instruct-Turbo';

    const array OPTIONS = [
        'temperature'     => 0.2,
        'max_new_tokens'  => 120,
        'response_format' => 'none',
        //'frequency_penalty' => -1,
    ];
}
