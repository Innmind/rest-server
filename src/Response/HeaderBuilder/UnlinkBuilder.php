<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Response\HeaderBuilder;

use Innmind\Rest\Server\Reference;
use Innmind\Http\Message\ServerRequest;
use Innmind\Immutable\MapInterface;

interface UnlinkBuilder
{
    /**
     * @param ServerRequest $request
     * @param Reference $from
     * @param MapInterface<Reference, MapInterface<string, ParameterInterface>> $tos
     *
     * @return MapInterface<string, HeaderInterface>
     */
    public function __invoke(
        ServerRequest $request,
        Reference $from,
        MapInterface $tos
    ): MapInterface;
}