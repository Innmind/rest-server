<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Response\HeaderBuilder;

use Innmind\Rest\Server\{
    Definition\HttpResource,
    Request\Range
};
use Innmind\Http\Message\ServerRequestInterface;
use Innmind\Specification\SpecificationInterface;
use Innmind\Immutable\{
    SetInterface,
    MapInterface
};

interface ListBuilderInterface
{
    /**
     * @param SetInterface<IdentityInterface> $identities
     * @param ServerRequestInterface $request
     * @param HttpResource $definition
     * @param SpecificationInterface $specification
     * @param Range $range
     *
     * @return MapInterface<string, HeaderInterface>
     */
    public function build(
        SetInterface $identities,
        ServerRequestInterface $request,
        HttpResource $definition,
        SpecificationInterface $specification = null,
        Range $range = null
    ): MapInterface;
}
