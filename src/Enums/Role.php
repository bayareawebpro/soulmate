<?php

namespace BayAreaWebPro\Soulmate\Enums;

enum Role: string
{
    case ASSISTANT = 'assistant';
    case SYSTEM = 'system';
    case USER = 'user';
    case TOOL = 'tool';
}
