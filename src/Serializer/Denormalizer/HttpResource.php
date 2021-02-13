<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Serializer\Denormalizer;

use Innmind\Rest\Server\{
    HttpResource\HttpResource as Resource,
    HttpResource\Property,
    Definition\HttpResource as ResourceDefinition,
    Definition\Property as PropertyDefinition,
    Definition\Access,
    Exception\DenormalizationException,
    Exception\HttpResourceDenormalizationException,
};
use Innmind\Immutable\{
    Map,
    Set,
};

final class HttpResource
{
    public function __invoke(
        array $data,
        ResourceDefinition $definition,
        Access $mask
    ): Resource {
        $errors = Map::of('string', DenormalizationException::class);
        /** @var array */
        $data = $data['resource'];

        /** @var Map<string, Property> */
        $properties = $definition
            ->properties()
            ->reduce(
                Map::of('string', Property::class),
                static function(
                    Map $properties,
                    string $name,
                    PropertyDefinition $definition
                ) use (
                    &$errors,
                    $data,
                    $mask
                ): Map {
                    if (
                        !$definition->access()->matches($mask) &&
                        isset($data[$name])
                    ) {
                        /**
                         * @psalm-suppress MixedMethodCall
                         * @psalm-suppress MixedAssignment
                         */
                        $errors = $errors->put(
                            $name,
                            new DenormalizationException('The field is not allowed')
                        );

                        return $properties;
                    }

                    if (
                        !isset($data[$name]) &&
                        $definition->access()->matches($mask)
                    ) {
                        if ($definition->isOptional()) {
                            return $properties;
                        }

                        /**
                         * @psalm-suppress MixedMethodCall
                         * @psalm-suppress MixedAssignment
                         */
                        $errors = $errors->put(
                            $name,
                            new DenormalizationException('The field is missing')
                        );

                        return $properties;
                    }

                    if (
                        !isset($data[$name]) &&
                        !$definition->access()->matches($mask)
                    ) {
                        return $properties;
                    }

                    try {
                        /** @psalm-suppress MixedArrayAccess */
                        $properties = $properties->put(
                            $name,
                            new Property(
                                $name,
                                $definition->type()->denormalize($data[$name])
                            )
                        );
                    } catch (DenormalizationException $e) {
                        /**
                         * @psalm-suppress MixedMethodCall
                         * @psalm-suppress MixedAssignment
                         */
                        $errors = $errors->put($name, $e);
                    }

                    return $properties;
                }
            );

        /** @psalm-suppress MixedMethodCall */
        if ($errors->size() > 0) {
            /** @psalm-suppress MixedArgument */
            throw new HttpResourceDenormalizationException($errors);
        }

        return new Resource($definition, $properties);
    }
}
