<?php declare(strict_types=1);

namespace BayAreaWebPro\Soulmate\Tests\Feature;

use BayAreaWebPro\Soulmate\Providers\MlStudioProvider;
use BayAreaWebPro\Soulmate\Soulmate;
use BayAreaWebPro\Soulmate\Tests\Mocks\EmbeddingProvider;
use BayAreaWebPro\Soulmate\Tests\TestCase;
use Illuminate\Support\Collection;

class ClientTest extends TestCase
{

    public function test_models(): void
    {
        $client = Soulmate::use(MlStudioProvider::class);
        $this->assertInstanceOf(Collection::class, $client->models());
        $this->assertTrue($client->models()->count() > 0);
    }

    public function test_embedding(): void
    {
        $embedding = Soulmate::use(EmbeddingProvider::class)
            ->embedding('This is a test');

        $this->assertInstanceOf(Collection::class, $embedding);
        $this->assertTrue($embedding->count() > 0);
    }
}
