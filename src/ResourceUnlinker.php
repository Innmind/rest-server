<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server;

interface ResourceUnlinker
{
    /**
     * @param Link[] $links All links must be removed atomically (all or nothing)
     */
    public function __invoke(Reference $from, Link ...$links): void;
}
