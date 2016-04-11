<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Definition;

use Innmind\Rest\Server\Exception\InvalidArgumentException;
use Innmind\Immutable\{
    MapInterface,
    Pair
};

class Directory
{
    private $name;
    private $children;
    private $definitions;

    public function __construct(
        string $name,
        MapInterface $children,
        MapInterface $definitions
    ) {
        if (
            (string) $children->keyType() !== 'string' ||
            (string) $children->valueType() !== self::class ||
            (string) $definitions->keyType() !== 'string' ||
            (string) $definitions->valueType() !== HttpResource::class
        ) {
            throw new InvalidArgumentException;
        }

        $this->name = $name;
        $this->children = $children;
        $this->definitions = $definitions;
    }

    public function name(): string
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
        $definitions = $this
            ->definitions
            ->map(function(string $name, HttpResource $definition) {
                return new Pair(
                    $this->name . '_' . $name,
                    $definition
                );
            });
        $this
            ->children
            ->foreach(function(string $name, self $child) use (&$definitions) {
                $definitions = $definitions->merge(
                    $child
                        ->flatten()
                        ->map(function(string $name, HttpResource $definition) {
                            return new Pair(
                                $this->name . '_' . $name,
                                $definition
                            );
                        })
                );
            });

        return $definitions;
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
