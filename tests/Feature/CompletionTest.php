<?php declare(strict_types=1);

namespace BayAreaWebPro\Soulmate\Tests\Feature;

use BayAreaWebPro\Soulmate\ChatCompletion\Response;
use BayAreaWebPro\Soulmate\Enums\Endpoint;
use BayAreaWebPro\Soulmate\Enums\FinishReason;
use BayAreaWebPro\Soulmate\Providers\MlStudioProvider;
use BayAreaWebPro\Soulmate\Soulmate;
use BayAreaWebPro\Soulmate\Tests\Fixtures\TextCompletion;
use BayAreaWebPro\Soulmate\Tests\TestCase;
use Illuminate\Support\Facades\Http;

class CompletionTest extends TestCase
{
    public function test_completion(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            MlStudioProvider::BASE_URL.Endpoint::COMPLETIONS->value => TextCompletion::make('blue')
        ]);

        $response = Soulmate::use(MlStudioProvider::class)
            ->system('You have a favorite color. Your favorite color is blue.')
            ->completion('What is your favorite color?');

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(FinishReason::STOP, $response->finishReason);
        $this->assertEquals('blue', $response->content->toString());
    }
}
