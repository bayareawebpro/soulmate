<?php declare(strict_types=1);

namespace BayAreaWebPro\Soulmate\Tests\Mocks;

use BayAreaWebPro\Soulmate\Providers\Provider;

class TokenProvider implements Provider
{
    const string BASE_URL = 'https://api.deepinfra.com/v1/openai';
    const string MODEL = 'meta-llama/Meta-Llama-3.1-70B-Instruct-Turbo';

    const array OPTIONS = [
        'temperature'     => 0.5,
        'response_format' => 'none',
    ];

    public function __construct(
        #[\SensitiveParameter]
        #[Config('soulmate.secret')]
        public string $token
    )
    {
        //
    }
}
