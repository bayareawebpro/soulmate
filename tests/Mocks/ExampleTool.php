<?php declare(strict_types=1);

namespace BayAreaWebPro\Soulmate\Tests\Mocks;

use BayAreaWebPro\Soulmate\Attributes\MethodContext;
use BayAreaWebPro\Soulmate\Attributes\ParameterContext;
use BayAreaWebPro\Soulmate\Chat\Conversation;

class ExampleTool
{
    public function __construct(protected Conversation $chatSession)
    {
        //
    }

    #[MethodContext('This function will get the current time')]
    public function getCurrentTime(): string
    {
        return now()->timezone('America/Los_Angeles')->toString();
    }

    #[MethodContext('This function will save the name')]
    #[ParameterContext('name', 'The name')]
    public function saveName(string $name): string
    {
        return "User Name: $name";
    }
}
