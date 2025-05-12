<?php declare(strict_types=1);

namespace BayAreaWebPro\Soulmate\Providers;

class MlStudioProvider implements Provider
{
    const string BASE_URL = 'http://127.0.0.1:1234/v1';
    const string MODEL = 'lmstudio-community/Qwen3-32B-GGUF';
    const array OPTIONS = [
        'temperature'     => 0.5,
        'max_new_tokens'  => 120,
        'response_format' => 'none',
    ];
}

