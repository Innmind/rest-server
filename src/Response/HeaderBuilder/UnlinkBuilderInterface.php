<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Response\HeaderBuilder;

use Innmind\Rest\Server\Reference;
use Innmind\Http\Message\ServerRequestInterface;
use Innmind\Immutable\MapInterface;

interface UnlinkBuilderInterface
{
    /**
     * @param ServerRequestInterface $request
     * @param Reference $from
     * @param MapInterface<Reference, MapInterface<string, ParameterInterface>> $tos
     *
     * @return MapInterface<string, HeaderInterface>
     */
    public function build(
        ServerRequestInterface $request,
        Reference $from,
        MapInterface $tos
    ): MapInterface;
}
