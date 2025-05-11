<?php declare(strict_types=1);

namespace BayAreaWebPro\Soulmate\Tests\Mocks;

use BayAreaWebPro\Soulmate\Attributes\MethodContext;
use BayAreaWebPro\Soulmate\Attributes\ParameterContext;
use BayAreaWebPro\Soulmate\Conversation;

class ExampleTool
{
    public function __construct(protected Conversation $chatSession)
    {
        //
    }

    #[MethodContext('This function will end the conversation')]
    #[ParameterContext('reason', 'Example: User input included profanity.')]
    public function default(string $reason): string
    {
        $this->chatSession->endConversation();

        return 'Conversation Ended';
    }
}
