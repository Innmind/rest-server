<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Definition;

use Innmind\Immutable\{
    Map,
    Set,
    Pair,
};

final class Directory
{
    private Name $name;
    /** @var Map<string, self> */
    private Map $children;
    /** @var Map<string, HttpResource> */
    private Map $definitions;
    /** @var Map<string, HttpResource>|null */
    private ?Map $flattened = null;

    /**
     * @param Map<string, self> $children
     * @param Map<string, HttpResource> $definitions
     */
    public function __construct(
        string $name,
        Map $children,
        Map $definitions
    ) {
        if (
            $children->keyType() !== 'string' ||
            $children->valueType() !== self::class
        ) {
            throw new \TypeError(sprintf(
                'Argument 2 must be of type Map<string, %s>',
                self::class
            ));
        }

        if (
            $definitions->keyType() !== 'string' ||
            $definitions->valueType() !== HttpResource::class
        ) {
            throw new \TypeError(sprintf(
                'Argument 3 must be of type Map<string, %s>',
                HttpResource::class
            ));
        }

        $this->name = new Name($name);
        $this->children = $children;
        $this->definitions = $definitions;
    }

    /**
     * @param Set<self> $children
     */
    public static function of(
        string $name,
        Set $children,
        HttpResource ...$definitions
    ): self {
        /** @var Map<string, HttpResource> */
        $map = Map::of('string', HttpResource::class);

        foreach ($definitions as $definition) {
            $map = $map->put($definition->name()->toString(), $definition);
        }

        /** @var Map<string, self> */
        $children = $children->reduce(
            Map::of('string', self::class),
            static function(Map $children, self $child): Map {
                return $children->put($child->name()->toString(), $child);
            },
        );

        return new self($name, $children, $map);
    }

    public function name(): Name
    {
        return $this->name;
    }

    public function child(string $name): self
    {
        return $this->children->get($name);
    }

    /**
     * @return Map<string, Directory>
     */
    public function children(): Map
    {
        return $this->children;
    }

    public function definition(string $name): HttpResource
    {
        return $this->definitions->get($name);
    }

    /**
     * @return Map<string, HttpResource>
     */
    public function definitions(): Map
    {
        return $this->definitions;
    }

    /**
     * @return Map<string, HttpResource>
     */
    public function flatten(): Map
    {
        if ($this->flattened instanceof Map) {
            return $this->flattened;
        }

        $definitions = $this
            ->definitions
            ->map(function(string $name, HttpResource $definition) {
                return new Pair(
                    $definition->name()->under($this->name)->toString(),
                    $definition
                );
            });
        /** @var Map<string, HttpResource> */
        $definitions = $this
            ->children
            ->reduce(
                $definitions,
                function(Map $carry, string $name, self $child): Map {
                    return $carry->merge(
                        $child
                            ->flatten()
                            ->map(function(string $name, HttpResource $definition) {
                                return new Pair(
                                    (new Name($name))->under($this->name)->toString(),
                                    $definition
                                );
                            })
                    );
                }
            );

        $this->flattened = $definitions;

        return $definitions;
    }

    public function toString(): string
    {
        return $this->name->toString();
    }
}
