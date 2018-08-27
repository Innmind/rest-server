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
    MapInterface,
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
        $errors = new Map('string', DenormalizationException::class);
        $data = $data['resource'];

        $properties = $definition
            ->properties()
            ->reduce(
                new Map('string', Property::class),
                function(
                    MapInterface $properties,
                    string $name,
                    PropertyDefinition $definition
                ) use (
                    &$errors,
                    $data,
                    $mask
                ): MapInterface {
                    if (
                        !$definition->access()->matches($mask) &&
                        isset($data[$name])
                    ) {
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
                        $properties = $properties->put(
                            $name,
                            new Property(
                                $name,
                                $definition->type()->denormalize($data[$name])
                            )
                        );
                    } catch (DenormalizationException $e) {
                        $errors = $errors->put($name, $e);
                    }

                    return $properties;
                }
            );

        if ($errors->size() > 0) {
            throw new HttpResourceDenormalizationException($errors);
        }

        return new Resource($definition, $properties);
    }
}
