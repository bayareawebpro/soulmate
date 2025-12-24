<?php declare(strict_types=1);

namespace BayAreaWebPro\Soulmate\Tests\Fixtures;

use Carbon\Carbon;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class TextCompletion
{
    static function make(string $message): PromiseInterface
    {
        return Http::response([
                "id" => Str::random(26),
                "object" => "text_completion",
                "created" => Carbon::now()->timestamp,
                "model" => "meta-llama-3.1-70b-instruct",
                "choices" => [
                    [
                        "index" => 0,
                        "logprobs" => null,
                        "text" => $message,
                        "finish_reason" => "stop"
                    ]
                ],
                "usage" => [
                    "total_tokens" => 0,
                    "prompt_tokens" => 0,
                    "completion_tokens" => 0,
                ],
                "stats" => [
                    //
                ]
            ]);
    }
}
