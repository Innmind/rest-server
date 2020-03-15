<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Serializer\Normalizer;

use Innmind\Rest\Server\Definition\{
    HttpResource,
    Property,
    AllowedLink,
    AllowedLink\Parameter,
};
use function Innmind\Immutable\unwrap;

final class Definition
{
    public function __invoke(HttpResource $resource): array
    {
        /** @psalm-suppress InvalidScalarArgument */
        $metas = \array_combine(
            unwrap($resource->metas()->keys()),
            unwrap($resource->metas()->values()),
        );

        return [
            'identity' => $resource->identity()->toString(),
            'properties' => $resource
                ->properties()
                ->reduce(
                    [],
                    function(array $carry, string $name, Property $property) {
                        $carry[$name] = [
                            'type' => $property->type()->toString(),
                            'access' => unwrap($property->access()->mask()),
                            'variants' => unwrap($property->variants()),
                            'optional' => $property->isOptional(),
                        ];

                        return $carry;
                    }
                ),
            'metas' => $metas,
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
