<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Serializer\Normalizer;

use Innmind\Rest\Server\Definition\{
    HttpResource,
    Property,
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
                    function(array $carry, string $type, string $path): array {
                        $carry[$type] = $path;

                        return $carry;
                    }
                ),
        ];
    }
}
