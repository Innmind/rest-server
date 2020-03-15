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
    private Map $children;
    private Map $definitions;
    private ?Map $flattened = null;

    public function __construct(
        string $name,
        Map $children,
        Map $definitions
    ) {
        if (
            (string) $children->keyType() !== 'string' ||
            (string) $children->valueType() !== self::class
        ) {
            throw new \TypeError(sprintf(
                'Argument 2 must be of type Map<string, %s>',
                self::class
            ));
        }

        if (
            (string) $definitions->keyType() !== 'string' ||
            (string) $definitions->valueType() !== HttpResource::class
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

    public static function of(
        string $name,
        Set $children,
        HttpResource ...$definitions
    ): self {
        $map = Map::of('string', HttpResource::class);

        foreach ($definitions as $definition) {
            $map = $map->put($definition->name()->toString(), $definition);
        }

        return new self(
            $name,
            $children->reduce(
                Map::of('string', self::class),
                static function(Map $children, self $child): Map {
                    return $children->put($child->name()->toString(), $child);
                }
            ),
            $map
        );
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
        $definitions = $this
            ->children
            ->reduce(
                $definitions,
                function(Map $carry, string $name, self $child) {
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
