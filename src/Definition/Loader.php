<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Definition;

use Innmind\Immutable\MapInterface;

interface Loader
{
    /**
     * Load this set of files and return a directory of definitions
     *
     * @return MapInterface<string, Directory>
     */
    public function __invoke(string ...$files): MapInterface;
}
