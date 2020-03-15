<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Definition;

use Innmind\Rest\Server\Exception\DefinitionNotFound;
use Innmind\Immutable\Map;

final class Locator
{
    private Directory $directory;
    /** @var Map<string, HttpResource> */
    private Map $cache;

    public function __construct(Directory $directory)
    {
        $this->directory = $directory;
        /** @var Map<string, HttpResource> */
        $this->cache = Map::of('string', HttpResource::class);
    }

    public function __invoke(string $path): HttpResource
    {
        if ($this->cache->contains($path)) {
            return $this->cache->get($path);
        }

        $resources = $this->directory->flatten();

        if (!$resources->contains($path)) {
            throw new DefinitionNotFound;
        }

        $resource = $resources->get($path);
        $this->cache = $this->cache->put($path, $resource);

        return $resource;
    }
}
