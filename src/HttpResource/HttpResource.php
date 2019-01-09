<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\HttpResource;

use Innmind\Rest\Server\{
    HttpResource as HttpResourceInterface,
    Definition\HttpResource as ResourceDefinition,
    Exception\DomainException
};
use Innmind\Immutable\{
    MapInterface,
    Map,
};

final class HttpResource implements HttpResourceInterface
{
    private $definition;
    private $properties;

    public function __construct(
        ResourceDefinition $definition,
        MapInterface $properties
    ) {
        if (
            (string) $properties->keyType() !== 'string' ||
            (string) $properties->valueType() !== Property::class
        ) {
            throw new \TypeError(sprintf(
                'Argument 2 must be of type MapInterface<string, %s>',
                Property::cass
            ));
        }

        $this->definition = $definition;
        $this->properties = $properties;

        $this
            ->properties
            ->foreach(function(string $name, Property $property) {
                if (!$this->definition->properties()->contains($name)) {
                    throw new DomainException;
                }
            });
    }

    public static function of(
        ResourceDefinition $definition,
        Property ...$properties
    ): self {
        $map = Map::of('string', Property::class);

        foreach ($properties as $property) {
            $map = $map->put($property->name(), $property);
        }

        return new self($definition, $map);
    }

    /**
     * {@inheritdoc}
     */
    public function definition(): ResourceDefinition
    {
        return $this->definition;
    }

    /**
     * {@inheritdoc}
     */
    public function property(string $name): Property
    {
        return $this->properties->get($name);
    }

    /**
     * {@inheritdoc}
     */
    public function has(string $name): bool
    {
        return $this->properties->contains($name);
    }

    /**
     * {@inheritdoc}
     */
    public function properties(): MapInterface
    {
        return $this->properties;
    }
}
