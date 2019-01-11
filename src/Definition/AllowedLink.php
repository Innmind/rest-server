<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Definition;

use Innmind\Rest\Server\{
    Definition\AllowedLink\Parameter,
    Link,
    Exception\DomainException,
    Exception\DefinitionNotFound,
};
use Innmind\Immutable\{
    Sequence,
    MapInterface,
    Map,
    SetInterface,
    Set,
    Str,
};

final class AllowedLink
{
    private $relationship;
    private $resourcePath;
    private $parameters;

    public function __construct(
        string $relationship,
        string $resourcePath,
        Parameter ...$parameters
    ) {
        if (
            Str::of($relationship)->empty() ||
            Str::of($resourcePath)->empty()
        ) {
            throw new DomainException;
        }

        $this->relationship = $relationship;
        $this->resourcePath = $resourcePath;
        $this->parameters = Sequence::of(...$parameters)->reduce(
            Map::of('string', Parameter::class),
            static function(MapInterface $parameters, Parameter $parameter): MapInterface {
                return $parameters->put(
                    $parameter->name(),
                    $parameter
                );
            }
        );
    }

    public function relationship(): string
    {
        return $this->relationship;
    }

    public function resourcePath(): string
    {
        return $this->resourcePath;
    }

    /**
     * @return SetInterface<Parameter>
     */
    public function parameters(): SetInterface
    {
        return Set::of(Parameter::class, ...$this->parameters->values());
    }

    public function accept(Locator $locate, Link $link): bool
    {
        if ($link->reference()->definition() !== $locate($this->resourcePath)) {
            return false;
        }

        if ($link->relationship() !== $this->relationship) {
            return false;
        }

        return $this->parameters->reduce(
            true,
            static function(bool $accept, string $parameter) use ($link): bool {
                if (!$link->has($parameter)) {
                    return false;
                }

                return $accept;
            }
        );
    }
}
