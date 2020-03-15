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
    Map,
    Set,
    Str,
};
use function Innmind\Immutable\unwrap;

final class AllowedLink
{
    private string $relationship;
    private string $resourcePath;
    private Map $parameters;

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
        $this->parameters = Sequence::of(Parameter::class, ...$parameters)->reduce(
            Map::of('string', Parameter::class),
            static function(Map $parameters, Parameter $parameter): Map {
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
     * @return Set<Parameter>
     */
    public function parameters(): Set
    {
        return Set::of(Parameter::class, ...unwrap($this->parameters->values()));
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
