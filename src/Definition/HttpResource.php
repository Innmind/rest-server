<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Definition;

use Innmind\Immutable\{
    MapInterface,
    Map,
    SetInterface,
};

final class HttpResource
{
    private $name;
    private $identity;
    private $properties;
    private $options;
    private $metas;
    private $gateway;
    private $rangeable = false;
    private $allowedLinks;

    public function __construct(
        string $name,
        Gateway $gateway,
        Identity $identity,
        SetInterface $properties,
        MapInterface $options = null,
        MapInterface $metas = null,
        MapInterface $allowedLinks = null
    ) {
        $options = $options ?? Map::of('scalar', 'variable');
        $metas = $metas ?? Map::of('scalar', 'variable');
        $allowedLinks = $allowedLinks ?? Map::of('string', 'string');

        if ((string) $properties->type() !== Property::class) {
            throw new \TypeError(sprintf(
                'Argument 4 must be of type SetInterface<%s>',
                Property::class
            ));
        }

        if (
            (string) $options->keyType() !== 'scalar' ||
            (string) $options->valueType() !== 'variable'
        ) {
            throw new \TypeError('Argument 5 must be of type MapInterface<scalar, variable>');
        }

        if (
            (string) $metas->keyType() !== 'scalar' ||
            (string) $metas->valueType() !== 'variable'
        ) {
            throw new \TypeError('Argument 6 must be of type MapInterface<scalar, variable>');
        }

        if (
            (string) $allowedLinks->keyType() !== 'string' ||
            (string) $allowedLinks->valueType() !== 'string'
        ) {
            throw new \TypeError('Argument 8 must be of type MapInterface<string, string>');
        }

        $this->name = new Name($name);
        $this->identity = $identity;
        $this->properties = $properties->reduce(
            Map::of('string', Property::class),
            static function(MapInterface $properties, Property $property): MapInterface {
                return $properties->put((string) $property->name(), $property);
            }
        );
        $this->options = $options;
        $this->metas = $metas;
        $this->gateway = $gateway;
        $this->allowedLinks = $allowedLinks;
    }

    public static function rangeable(
        string $name,
        Gateway $gateway,
        Identity $identity,
        SetInterface $properties,
        MapInterface $options = null,
        MapInterface $metas = null,
        MapInterface $allowedLinks = null
    ): self {
        $self = new self($name, $gateway, $identity, $properties, $options, $metas, $allowedLinks);
        $self->rangeable = true;

        return $self;
    }

    public function name(): Name
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

    /**
     * @return MapInterface<scalar, variable>
     */
    public function options(): MapInterface
    {
        return $this->options;
    }

    /**
     * @return MapInterface<scalar, variable>
     */
    public function metas(): MapInterface
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
        return (string) $this->name;
    }
}
