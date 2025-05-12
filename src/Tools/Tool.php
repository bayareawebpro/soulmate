<?php

namespace BayAreaWebPro\Soulmate\Tools;

use BayAreaWebPro\Soulmate\Attributes\MethodContext;
use BayAreaWebPro\Soulmate\Attributes\ParameterContext;
use BayAreaWebPro\Soulmate\Exceptions\InvalidParameterType;
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
//
//        $config = [
//            'name'        => $this->method,
//            'description' => $description,
//        ];
//
//        Arr::set($config, 'parameters', [
//            'type'       => 'object',
//            'properties' => $properties->count() ? $properties->toArray() : new \stdClass,
//        ]);
//        Arr::set($config, 'parameters.required',$required->count() ? $required->toArray() : new \stdClass);

        return [
            'type'     => 'function',
            'function' => [
                'name'        => $this->method,
                'description' => $description,
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => $properties->count() ? $properties->toArray() : new \stdClass,
                    'required'   => $required->count() ? $required->toArray() : new \stdClass,
                ],
            ]
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

        $methodName = $reflected->getName();

        foreach ($reflected->getParameters() as $param) {
            $paramName = $param->getName();
            $paramType = $param->getType();
            $context    = $parameterContexts->firstWhere('name', '=', $paramName);

            if (!$context) {
                throw new MissingMethodContext("{$methodName}({$paramName}) does not have a context");
            }

            if(!$paramType instanceof \ReflectionNamedType) {
                throw new InvalidParameterType("Tool method parameter {$methodName}({$paramName}) must be ReflectionNamedType.");
            }

            if(!$paramType->isBuiltin()) {
                throw new InvalidParameterType("Tool method parameter {$methodName}({$paramName}) be built-in type (int, float, double, or string).");
            }

            $properties->put($paramName, [
                'type' => match($paramType->getName()){
                    'int'       => 'int',
                    'float'     => 'float',
                    'double'    => 'double',
                    'string'    => 'string',
                    default     => throw new InvalidParameterType,
                },
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
