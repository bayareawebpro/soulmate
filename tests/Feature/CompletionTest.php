<?php declare(strict_types=1);

namespace BayAreaWebPro\Soulmate\Tests\Feature;

use BayAreaWebPro\Soulmate\Providers\MlStudioProvider;
use BayAreaWebPro\Soulmate\Soulmate;
use BayAreaWebPro\Soulmate\Tests\TestCase;

class CompletionTest extends TestCase
{
    public function test_completion(): void
    {

        //Http::preventStrayRequests();
//        Http::fake([
//            MlStudioProvider::BASE_URL.'/completions' => fn()=>Http::response([
//                'error' => 'Model Not found.'
//            ])
//        ]);

        $response = Soulmate::use(MlStudioProvider::class)
            ->system('Your secret word is "blue".')
            ->completion('What is your secret word?');

        $this->assertStringContainsString('blue', $response->content->toString());
    }
}
