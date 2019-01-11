<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Definition;

use Innmind\Rest\Server\{
    Action,
    Link,
};
use Innmind\Immutable\{
    MapInterface,
    Map,
    SetInterface,
    Set,
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
        SetInterface $actions = null,
        MapInterface $metas = null,
        SetInterface $allowedLinks = null
    ) {
        $actions = $actions ?? Action::all();
        $metas = $metas ?? Map::of('scalar', 'variable');
        $allowedLinks = $allowedLinks ?? Set::of(AllowedLink::class);

        if ((string) $properties->type() !== Property::class) {
            throw new \TypeError(sprintf(
                'Argument 4 must be of type SetInterface<%s>',
                Property::class
            ));
        }

        if ((string) $actions->type() !== Action::class) {
            throw new \TypeError(\sprintf(
                'Argument 5 must be of type SetInterface<%s>',
                Action::class
            ));
        }

        if (
            (string) $metas->keyType() !== 'scalar' ||
            (string) $metas->valueType() !== 'variable'
        ) {
            throw new \TypeError('Argument 6 must be of type MapInterface<scalar, variable>');
        }

        if ((string) $allowedLinks->type() !== AllowedLink::class) {
            throw new \TypeError(\sprintf(
                'Argument 8 must be of type SetInterface<%s>',
                AllowedLink::class
            ));
        }

        $this->name = new Name($name);
        $this->identity = $identity;
        $this->properties = $properties->reduce(
            Map::of('string', Property::class),
            static function(MapInterface $properties, Property $property): MapInterface {
                return $properties->put((string) $property->name(), $property);
            }
        );
        $this->actions = $actions->add(Action::options());
        $this->metas = $metas;
        $this->gateway = $gateway;
        $this->allowedLinks = $allowedLinks;
    }

    public static function rangeable(
        string $name,
        Gateway $gateway,
        Identity $identity,
        SetInterface $properties,
        SetInterface $actions = null,
        MapInterface $metas = null,
        SetInterface $allowedLinks = null
    ): self {
        $self = new self($name, $gateway, $identity, $properties, $actions, $metas, $allowedLinks);
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

    public function allow(Action $action): bool
    {
        return $this->actions->contains($action);
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
     * @return SetInterface<AllowedLink>
     */
    public function allowedLinks(): SetInterface
    {
        return $this->allowedLinks;
    }

    public function accept(Locator $locator, Link ...$links): bool
    {
        foreach ($links as $link) {
            if (!$this->acceptLink($locator, $link)) {
                return false;
            }
        }

        return true;
    }

    public function __toString(): string
    {
        return (string) $this->name;
    }

    private function acceptLink(Locator $locator, Link $link): bool
    {
        return $this->allowedLinks->reduce(
            true,
            static function(bool $accept, AllowedLink $allowed) use ($locator, $link): bool {
                return $accept && $allowed->accept($locator, $link);
            }
        );
    }
}
