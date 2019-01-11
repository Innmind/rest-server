<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Serializer\Normalizer;

use Innmind\Rest\Server\Definition\{
    HttpResource,
    Property,
    AllowedLink,
    AllowedLink\Parameter,
};

final class Definition
{
    public function __invoke(HttpResource $resource): array
    {
        return [
            'identity' => (string) $resource->identity(),
            'properties' => $resource
                ->properties()
                ->reduce(
                    [],
                    function(array $carry, string $name, Property $property) {
                        $carry[$name] = [
                            'type' => (string) $property->type(),
                            'access' => $property
                                ->access()
                                ->mask()
                                ->toPrimitive(),
                            'variants' => $property
                                ->variants()
                                ->toPrimitive(),
                            'optional' => $property->isOptional(),
                        ];

                        return $carry;
                    }
                ),
            'metas' => array_combine(
                $resource->metas()->keys()->toPrimitive(),
                $resource->metas()->values()->toPrimitive()
            ),
            'rangeable' => $resource->isRangeable(),
            'linkable_to' => $resource
                ->allowedLinks()
                ->reduce(
                    [],
                    function(array $carry, AllowedLink $allowed): array {
                        $carry[] = [
                            'relationship' => $allowed->relationship(),
                            'resource_path' => $allowed->resourcePath(),
                            'parameters' => $allowed->parameters()->reduce(
                                [],
                                static function(array $carry, Parameter $parameter): array {
                                    $carry[] = $parameter->name();

                                    return $carry;
                                }
                            ),
                        ];

                        return $carry;
                    }
                ),
        ];
    }
}
