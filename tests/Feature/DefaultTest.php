<?php declare(strict_types=1);

namespace BayAreaWebPro\Soulmate\Tests\Feature;

use BayAreaWebPro\Soulmate\Tests\TestCase;
use Carbon\Carbon;
use Illuminate\Support\Fluent;
use Illuminate\Support\Str;
use Workbench\App\Models\User;

class DefaultTest extends TestCase
{
    public function test_default(): void
    {
        //dump(User::factory()->create());
        //$this->assertDatabaseHas('users');

        $data = Fluent::make([
            "object" => "text_completion",
            "model" => "meta-llama-3.1-70b-instruct",
            "choices" => [
                [
                    "index" => 0,
                    "logprobs" => null,
                    "text" => 'test',
                    "finish_reason" => "stop"
                ]
            ],
            "usage" => [
                "total_tokens" => 0,
                "prompt_tokens" => 0,
                "completion_tokens" => 0,
            ]
        ]);
    }
}
