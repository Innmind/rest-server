<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Definition;

use Innmind\Rest\Server\{
    Action,
    Link,
};
use Innmind\Immutable\{
    Map,
    Set,
};

final class HttpResource
{
    private Name $name;
    private Identity $identity;
    /** @var Map<string, Property> */
    private Map $properties;
    /** @var Set<Action> */
    private Set $actions;
    /** @var Map<scalar, scalar|array> */
    private Map $metas;
    private Gateway $gateway;
    private bool $rangeable = false;
    /** @var Set<AllowedLink> */
    private Set $allowedLinks;

    /**
     * @param Set<Property> $properties
     * @param Set<Action>|null $actions
     * @param Set<AllowedLink>|null $allowedLinks
     * @param Map<scalar, scalar|array>|null $metas
     */
    public function __construct(
        string $name,
        Gateway $gateway,
        Identity $identity,
        Set $properties,
        Set $actions = null,
        Set $allowedLinks = null,
        Map $metas = null
    ) {
        $actions = $actions ?? Action::all();
        $metas = $metas ?? Map::of('scalar', 'scalar|array');
        $allowedLinks = $allowedLinks ?? Set::of(AllowedLink::class);

        if ($properties->type() !== Property::class) {
            throw new \TypeError(\sprintf(
                'Argument 4 must be of type Set<%s>',
                Property::class
            ));
        }

        if ($actions->type() !== Action::class) {
            throw new \TypeError(\sprintf(
                'Argument 5 must be of type Set<%s>',
                Action::class
            ));
        }

        if ($allowedLinks->type() !== AllowedLink::class) {
            throw new \TypeError(\sprintf(
                'Argument 6 must be of type Set<%s>',
                AllowedLink::class
            ));
        }

        if (
            $metas->keyType() !== 'scalar' ||
            $metas->valueType() !== 'scalar|array'
        ) {
            throw new \TypeError('Argument 7 must be of type Map<scalar, scalar|array>');
        }

        $this->name = new Name($name);
        $this->identity = $identity;
        /** @var Map<string, Property> */
        $this->properties = $properties->reduce(
            Map::of('string', Property::class),
            static function(Map $properties, Property $property): Map {
                return $properties->put($property->name(), $property);
            }
        );
        $this->actions = $actions->add(Action::options());
        $this->metas = $metas;
        $this->gateway = $gateway;
        $this->allowedLinks = $allowedLinks;
    }

    /**
     * @param Set<Property> $properties
     * @param Set<Action>|null $actions
     * @param Set<AllowedLink>|null $allowedLinks
     * @param Map<scalar, scalar|array>|null $metas
     */
    public static function rangeable(
        string $name,
        Gateway $gateway,
        Identity $identity,
        Set $properties,
        Set $actions = null,
        Set $allowedLinks = null,
        Map $metas = null
    ): self {
        $self = new self($name, $gateway, $identity, $properties, $actions, $allowedLinks, $metas);
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
     * @return Map<string, Property>
     */
    public function properties(): Map
    {
        return $this->properties;
    }

    public function allow(Action $action): bool
    {
        return $this->actions->contains($action);
    }

    /**
     * @return Map<scalar, scalar|array>
     */
    public function metas(): Map
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
     * @return Set<AllowedLink>
     */
    public function allowedLinks(): Set
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

    public function toString(): string
    {
        return $this->name->toString();
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
