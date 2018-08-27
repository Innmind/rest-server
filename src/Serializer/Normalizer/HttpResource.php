<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Serializer\Normalizer;

use Innmind\Rest\Server\{
    HttpResource\HttpResource as Resource,
    HttpResource\Property,
    Definition\Access,
    Exception\NormalizationException,
    Exception\HttpResourceNormalizationException,
};
use Innmind\Immutable\Map;

final class HttpResource
{
    public function __invoke(Resource $resource): array
    {
        $errors = new Map('string', NormalizationException::class);

        $definition = $resource->definition();
        $mask = new Access(Access::READ);
        $data = $resource
            ->properties()
            ->reduce(
                [],
                function(
                    array $data,
                    string $name,
                    Property $property
                ) use (
                    &$errors,
                    $definition,
                    $mask
                ): array {
                    $propertyDefinition = $definition
                        ->properties()
                        ->get($name);

                    if (!$propertyDefinition->access()->matches($mask)) {
                        return $data;
                    }

                    try {
                        $data[$name] = $propertyDefinition
                            ->type()
                            ->normalize($property->value());
                    } catch (NormalizationException $e) {
                        $errors = $errors->put($name, $e);
                    }

                    return $data;
                }
            );

        if ($errors->size() > 0) {
            throw new HttpResourceNormalizationException($errors);
        }

        return ['resource' => $data];
    }
}
