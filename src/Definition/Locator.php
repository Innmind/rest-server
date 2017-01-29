<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Definition;

use Innmind\Rest\Server\Exception\{
    DefinitionNotFoundException,
    InvalidArgumentException
};
use Innmind\Immutable\{
    MapInterface,
    Map
};

final class Locator
{
    private $directories;
    private $cache;

    public function __construct(MapInterface $directories)
    {
        if (
            (string) $directories->keyType() !== 'string' ||
            (string) $directories->valueType() !== Directory::class
        ) {
            throw new InvalidArgumentException;
        }

        $this->directories = $directories;
        $this->cache = new Map('string', HttpResource::class);
    }

    public function locate(string $path): HttpResource
    {
        if ($this->cache->contains($path)) {
            return $this->cache->get($path);
        }

        $resource = $this
            ->directories
            ->reduce(
                null,
                function($carry, string $dirName, Directory $directory) use ($path) {
                    if ($carry instanceof $directory) {
                        return $carry;
                    }

                    $resources = $directory->flatten();

                    if ($resources->contains($path)) {
                        return $resources->get($path);
                    }
                }
            );

        if (!$resource instanceof HttpResource) {
            throw new DefinitionNotFoundException;
        }

        $this->cache = $this->cache->put($path, $resource);

        return $resource;
    }
}