<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Definition;

use Innmind\Immutable\{
    SetInterface,
    MapInterface
};

interface LoaderInterface
{
    /**
     * Load this set of files and return a directory of definitions
     *
     * @param SetInterface<string> $files
     *
     * @return MapInterface<string, Directory>
     */
    public function load(SetInterface $files): MapInterface;
}
