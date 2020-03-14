<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Definition;

use Innmind\Immutable\{
    MapInterface,
    Map,
    SetInterface,
    Pair,
};

final class Directory
{
    private Name $name;
    private MapInterface $children;
    private MapInterface $definitions;
    private ?MapInterface $flattened = null;

    public function __construct(
        string $name,
        MapInterface $children,
        MapInterface $definitions
    ) {
        if (
            (string) $children->keyType() !== 'string' ||
            (string) $children->valueType() !== self::class
        ) {
            throw new \TypeError(sprintf(
                'Argument 2 must be of type MapInterface<string, %s>',
                self::class
            ));
        }

        if (
            (string) $definitions->keyType() !== 'string' ||
            (string) $definitions->valueType() !== HttpResource::class
        ) {
            throw new \TypeError(sprintf(
                'Argument 3 must be of type MapInterface<string, %s>',
                HttpResource::class
            ));
        }

        $this->name = new Name($name);
        $this->children = $children;
        $this->definitions = $definitions;
    }

    public static function of(
        string $name,
        SetInterface $children,
        HttpResource ...$definitions
    ): self {
        $map = Map::of('string', HttpResource::class);

        foreach ($definitions as $definition) {
            $map = $map->put((string) $definition->name(), $definition);
        }

        return new self(
            $name,
            $children->reduce(
                Map::of('string', self::class),
                static function(MapInterface $children, self $child): MapInterface {
                    return $children->put((string) $child->name(), $child);
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
     * @return MapInterface<string, Directory>
     */
    public function children(): MapInterface
    {
        return $this->children;
    }

    public function definition(string $name): HttpResource
    {
        return $this->definitions->get($name);
    }

    /**
     * @return MapInterface<string, HttpResource>
     */
    public function definitions(): MapInterface
    {
        return $this->definitions;
    }

    /**
     * @return MapInterface<string, HttpResource>
     */
    public function flatten(): MapInterface
    {
        if ($this->flattened instanceof MapInterface) {
            return $this->flattened;
        }

        $definitions = $this
            ->definitions
            ->map(function(string $name, HttpResource $definition) {
                return new Pair(
                    (string) $definition->name()->under($this->name),
                    $definition
                );
            });
        $definitions = $this
            ->children
            ->reduce(
                $definitions,
                function(MapInterface $carry, string $name, self $child) {
                    return $carry->merge(
                        $child
                            ->flatten()
                            ->map(function(string $name, HttpResource $definition) {
                                return new Pair(
                                    (string) (new Name($name))->under($this->name),
                                    $definition
                                );
                            })
                    );
                }
            );

        $this->flattened = $definitions;

        return $definitions;
    }

    public function __toString(): string
    {
        return (string) $this->name;
    }
}
