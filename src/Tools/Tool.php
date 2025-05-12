<?php

namespace BayAreaWebPro\Soulmate\Tools;

use BayAreaWebPro\Soulmate\Attributes\MethodContext;
use BayAreaWebPro\Soulmate\Attributes\ParameterContext;
use BayAreaWebPro\Soulmate\Exceptions\MissingMethodContext;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;

class Tool implements Arrayable
{

    public function __construct(public string $class, public string $method)
    {
        //
    }

    public function execute(array $arguments): mixed
    {
        return App::call([App::make($this->class), $this->method], $arguments);
    }

    public function toArray(): array
    {
        $reflected = new \ReflectionMethod($this->class, $this->method);
        $description = $this->getMethodDescription($reflected);
        [$required, $properties] = $this->getParameterDescriptions($reflected);

        $config = [
            'name'        => $this->method,
            'description' => $description,
        ];

        Arr::set($config, 'parameters', [
            'type'       => 'object',
            'properties' => $properties->count() ? $properties->toArray() : new \stdClass,
        ]);
        Arr::set($config, 'parameters.required', $required->toArray());

        return [
            'type'     => 'function',
            'function' => $config
        ];
    }

    protected function getMethodDescription(\ReflectionMethod $reflected)
    {
        $context = Arr::first($reflected->getAttributes(MethodContext::class));

        if (!$context) {
            throw new MissingMethodContext("{$reflected->getName()} does not have a context");
        }

        return $context->newInstance()->value;
    }


    protected function getParameterDescriptions(\ReflectionMethod $reflected): array
    {
        $required = Collection::make();
        $properties = Collection::make();
        $parameterContexts = Collection::make($reflected->getAttributes(ParameterContext::class))
            ->map(fn(\ReflectionAttribute $attribute) => $attribute->newInstance());

        foreach ($reflected->getParameters() as $param) {

            $context = $parameterContexts->firstWhere('name', '=', $param->getName());

            if (!$context) {
                throw new MissingMethodContext("{$reflected->getName()}({$param->getName()}) does not have a context");
            }

            $properties->put($param->getName(), [
                'type'                 => $param->getType()->getName(),
                'description'          => $context->value,
                'additionalProperties' => false,
            ]);

            if (!($param->isOptional() || $param->allowsNull())) {
                $required->push($param->getName());
            }
        }
        return [$required, $properties];
    }
}
