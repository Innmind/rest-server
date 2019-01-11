<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server;

interface ResourceLinker
{
    /**
     * @param Link[] $links All links must be created atomically (all or nothing)
     */
    public function __invoke(Reference $from, Link ...$links): void;
}
