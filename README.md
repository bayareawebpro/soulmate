# Soulmate

 **Simple OpenAI-Compatible API Client for Laravel**

![CI](https://github.com/bayareawebpro/soulmate/workflows/ci/badge.svg)
![Coverage](https://codecov.io/gh/bayareawebpro/soulmate/branch/master/graph/badge.svg)
![Downloads](https://img.shields.io/packagist/dt/bayareawebpro/soulmate.svg)
![Version](https://img.shields.io/github/v/release/bayareawebpro/soulmate.svg)
![License](https://img.shields.io/badge/License-MIT-success.svg)

```bash
composer require bayareawebpro/soulmate
```

## API Provider Configuration:

Create your own providers by implementing the `Provider` contract.

```php
<?php declare(strict_types=1);

namespace App\Soulmate\Providers;

use Illuminate\Container\Attributes\Config;

use BayAreaWebPro\Soulmate\Providers\Provider;

class MlStudioProvider implements Provider
{
    const string BASE_URL = 'http://127.0.0.1:1234/v1';
    
    const string MODEL = 'lmstudio-community/Qwen3-32B-GGUF';
    
    const array OPTIONS = [
        'temperature'     => 0.5,
        'max_new_tokens'  => 120,
        'response_format' => 'none',
    ];
    
    public function __construct(
        #[\SensitiveParameter]
        #[Config('my.secret')]
        public string $secret
    )
    {
        //
    }
}
```

## Create Soulmate Instance

Create a new instance of Soulmate by passing in your chosen provider. 

```php
use BayAreaWebPro\Soulmate\Soulmate;
use BayAreaWebPro\Soulmate\Providers\MlStudioProvider;

$soul = Soulmate::use(new MlStudioProvider);
```

## HTTP Timeouts

Setup HTTP timeout limits to insure complex requests are aborted if they take too long.

```php
$soul = Soulmate::use(new MlStudioProvider)
    ->connectTimeout(10) // Connection timeout in seconds.
    ->timeout(60);       // Request timeout in seconds.
```

## System Prompts

Pass in a system prompt to be injected with out-bound requests. 

```php
$soul = Soulmate::use(new MlStudioProvider)
    ->system(<<<PROMPT
    # You are a helpful assistant.
    PROMPT)
```

## Standard Completions

```php
$soul = Soulmate::use(new MlStudioProvider);

$response = $soul->completion("Which US state is most famous for it's cheese?");

echo $response->content; // Wisconsin is the # 1 cheese-producing state, making 26% of the country's cheese.
```

## Chat Completions

```php
$soul = Soulmate::use(new MlStudioProvider)
    ->system(<<<PROMPT
    # You are a helpful assistant.
    * Ask the user for tasks.
    PROMPT);

$response = $soul->chat([
    new Message(Role::USER, 'Hi, my name is Joe.'),
]);

echo $response->content; // Hi Joe, what can I do for you today?
```

## Chat Completions Tool Usage

```php
$soul->tool(ExampleTool::class, 'getCurrentTime');
```

### Create Tool Class

Decorate your tool methods with `MethodContext` and `ParameterContext` attributes to describe how tools are used.

```php
<?php declare(strict_types=1);

namespace App\Soulmate\Tools;

use BayAreaWebPro\Soulmate\Attributes\MethodContext;
use BayAreaWebPro\Soulmate\Attributes\ParameterContext;

use Illuminate\Support\Facades\Auth;

class ExampleTool
{
    public function __construct()
    {
        // Inject services as needed.
    }

    #[MethodContext('This function will save the username')]
    #[ParameterContext('username', 'Example: john-doe')]
    public function saveName(string $username): string
    {
        // Your logic here.
        Auth::user()->update([
            'username' => $username
        ]);
        
        // Return data to the LLM.
        return "Username: $username";
    }
}

```

### Register Tool Methods

```php
use BayAreaWebPro\Soulmate\Soulmate;
use BayAreaWebPro\Soulmate\Providers\MlStudioProvider;

$response = Soulmate::use(new MlStudioProvider)
    ->tool(ExampleTool::class, 'getCurrentTime')
    ->chat([
        new Message(Role::USER, 'What time is it?'),
    ]);

echo $response->content; // The current time is 12:56PM PST.
```

### Testing

``` bash
composer test
composer lint
```
