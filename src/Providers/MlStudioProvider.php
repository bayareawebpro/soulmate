<?php declare(strict_types=1);

namespace BayAreaWebPro\Soulmate\Providers;

class MlStudioProvider implements Provider
{
    const string BASE_URL = 'http://127.0.0.1:1234/v1';
    const string MODEL = 'meta-llama-3.1-70b-instruct';
    const array OPTIONS = [
        'temperature'     => 0.5,
        'response_format' => 'none',
    ];
}

