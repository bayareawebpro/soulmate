<?php declare(strict_types=1);

namespace BayAreaWebPro\Soulmate\Tests\Mocks;

use BayAreaWebPro\Soulmate\Providers\Provider;

class EmbeddingProvider implements Provider
{
    const string BASE_URL = 'http://127.0.0.1:1234/v1';
    const string MODEL = 'text-embedding-nomic-embed-text-v1.5';
    const array OPTIONS = [

    ];
}

