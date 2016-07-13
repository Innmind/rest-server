<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Definition;

use Innmind\Rest\Server\Exception\InvalidArgumentException;
use Innmind\Immutable\{
    MapInterface,
    CollectionInterface
};

final class HttpResource
{
    private $name;
    private $identity;
    private $properties;
    private $options;
    private $metas;
    private $gateway;
    private $rangeable;
    private $allowedLinks;

    public function __construct(
        string $name,
        Identity $identity,
        MapInterface $properties,
        CollectionInterface $options,
        CollectionInterface $metas,
        Gateway $gateway,
        bool $rangeable,
        MapInterface $allowedLinks
    ) {
        if (
            (string) $properties->keyType() !== 'string' ||
            (string) $properties->valueType() !== Property::class ||
            (string) $allowedLinks->keyType() !== 'string' ||
            (string) $allowedLinks->valueType() !== 'string'
        ) {
            throw new InvalidArgumentException;
        }

        $this->name = $name;
        $this->identity = $identity;
        $this->properties = $properties;
        $this->options = $options;
        $this->metas = $metas;
        $this->gateway = $gateway;
        $this->rangeable = $rangeable;
        $this->allowedLinks = $allowedLinks;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function identity(): Identity
    {
        return $this->identity;
    }

    /**
     * @return MapInterface<string, Property>
     */
    public function properties(): MapInterface
    {
        return $this->properties;
    }

    public function options(): CollectionInterface
    {
        return $this->options;
    }

    public function metas(): CollectionInterface
    {
        return $this->metas;
    }

    public function gateway(): Gateway
    {
        return $this->gateway;
    }

    public function isRangeable(): bool
    {
        return $this->rangeable;
    }

    /**
     * @return MapInterface<string, string> Relationship type as key and definition path as value
     */
    public function allowedLinks(): MapInterface
    {
        return $this->allowedLinks;
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
