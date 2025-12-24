<?php declare(strict_types=1);

namespace BayAreaWebPro\Soulmate\Tests\Feature;

use BayAreaWebPro\Soulmate\ChatCompletion\Message;
use BayAreaWebPro\Soulmate\Enums\Role;
use BayAreaWebPro\Soulmate\Providers\MlStudioProvider;
use BayAreaWebPro\Soulmate\Soulmate;
use BayAreaWebPro\Soulmate\Tests\Mocks\ExampleTool;
use BayAreaWebPro\Soulmate\Tests\TestCase;
use Illuminate\Support\Facades\Http;

class ConversationTest extends TestCase
{
    public function test_example(): void
    {

        //Http::preventStrayRequests();
//        Http::fake([
//            MlStudioProvider::BASE_URL.'/completions' => fn()=>Http::response([
//                'error' => 'Model Not found.'
//            ])
//        ]);

        $client = Soulmate::use(MlStudioProvider::class)
            ->tool(ExampleTool::class, 'getCurrentTime')
            ->system(<<<TEXT
            # You are a helpful assistant.
            /no_think
            TEXT);

        $message = $client->chat([
            new Message(Role::USER, 'What time is it?'),
        ]);

        $this->assertStringContainsString('current time is', $message->content);
    }
}
