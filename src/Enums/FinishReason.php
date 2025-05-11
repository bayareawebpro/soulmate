<?php

namespace BayAreaWebPro\Soulmate\Enums;

enum FinishReason: string
{
    case TOOL_CALLS = 'tool_calls';
    case CONTENT_FILTER = 'content_filter';
    case LENGTH = 'length';
    case STOP = 'stop';
}
