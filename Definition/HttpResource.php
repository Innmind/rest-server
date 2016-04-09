<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Definition;

use Innmind\Rest\Server\Exception\InvalidArgumentException;
use Innmind\Immutable\{
    MapInterface,
    CollectionInterface
};

class HttpResource
{
    private $identity;
    private $properties;
    private $options;
    private $metas;
    private $gateway;

    public function __construct(
        Identity $identity,
        MapInterface $properties,
        CollectionInterface $options,
        CollectionInterface $metas,
        Gateway $gateway
    ) {
        if (
            (string) $properties->keyType() !== 'string' ||
            (string) $properties->valueType() !== Property::class
        ) {
            throw new InvalidArgumentException;
        }

        $this->identity = $identity;
        $this->properties = $properties;
        $this->options = $options;
        $this->metas = $metas;
        $this->gateway = $gateway;
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
}
