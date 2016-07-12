<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server;

use Innmind\Immutable\MapInterface;

interface ResourceUnlinkerInterface
{
    /**
     * @param Reference $from
     * @param MapInterface<Reference, MapInterface<string, ParameterInterface>> $tos
     *     All relationships must be removed atomically (all or nothing)
     *
     * @return void
     */
    public function __invoke(Reference $from, MapInterface $tos);
}
