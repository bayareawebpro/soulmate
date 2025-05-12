<?php declare(strict_types=1);

namespace BayAreaWebPro\Soulmate\Tests\Feature;

use BayAreaWebPro\Soulmate\Chat\Message;
use BayAreaWebPro\Soulmate\Enums\Role;
use BayAreaWebPro\Soulmate\Providers\MlStudioProvider;
use BayAreaWebPro\Soulmate\Soulmate;
use BayAreaWebPro\Soulmate\Tests\Mocks\ExampleTool;
use BayAreaWebPro\Soulmate\Tests\TestCase;

class ClientTest extends TestCase
{
    public function test_example(): void
    {

        $client = Soulmate::use(new MlStudioProvider)
            ->tool(ExampleTool::class, 'getCurrentTime')
            ->system(<<<TEXT
            # You are a helpful assistant.
            /no_think
            TEXT);

        $response = $client->chat([
            new Message(Role::USER, 'What time is it?'),
        ]);

        $response = $client->completion('What time is it? /no_think');

        $response->dump();

    }
}
