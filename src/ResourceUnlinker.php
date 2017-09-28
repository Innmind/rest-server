<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server;

use Innmind\Immutable\MapInterface;

interface ResourceUnlinker
{
    /**
     * @param MapInterface<Reference, MapInterface<string, ParameterInterface>> $tos
     *     All relationships must be removed atomically (all or nothing)
     */
    public function __invoke(Reference $from, MapInterface $tos): void;
}
