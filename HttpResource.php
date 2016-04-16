<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server;

use Innmind\Rest\Server\{
    Definition\HttpResource as ResourceDefinition,
    Exception\InvalidArgumentException
};
use Innmind\Immutable\MapInterface;

class HttpResource implements HttpResourceInterface
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
            throw new InvalidArgumentException;
        }

        $this->definition = $definition;
        $this->properties = $properties;
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