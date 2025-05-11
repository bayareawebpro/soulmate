<?php declare(strict_types=1);

namespace BayAreaWebPro\Soulmate\Tests\Feature;

use BayAreaWebPro\Soulmate\Chat\Message;
use BayAreaWebPro\Soulmate\Enums\Role;
use BayAreaWebPro\Soulmate\Providers\DeepInfraProvider;
use BayAreaWebPro\Soulmate\Soulmate;
use BayAreaWebPro\Soulmate\Tests\Mocks\ExampleTool;
use BayAreaWebPro\Soulmate\Tests\TestCase;

class ClientTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_example(): void
    {
        $client = Soulmate::use(new DeepInfraProvider)
            ->tool(ExampleTool::class, 'default')
            ->system(<<<TEXT
            # You are a helpful assistant.
            TEXT);

        $response = $client->chat([
            new Message(Role::ASSISTANT, 'Hello, what\'s your name?'),
            new Message(Role::USER, 'Dan'),
        ]);

        $client->conversation->toCollection()->reverse()->take(3)->reverse()->dump();

        $client->conversation->toCollection()->where('role', 'tool')->dump();
    }
}
