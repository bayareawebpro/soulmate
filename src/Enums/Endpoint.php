<?php

namespace BayAreaWebPro\Soulmate\Enums;

enum Endpoint: string
{
    case MODELS = '/models';
    case EMBEDDINGS = '/embeddings';
    case COMPLETIONS = '/completions';
    case CHAT_COMPLETIONS = '/chat/completions';
}
