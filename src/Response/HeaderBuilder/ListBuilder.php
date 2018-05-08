<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Response\HeaderBuilder;

use Innmind\Rest\Server\{
    Definition\HttpResource,
    Request\Range,
};
use Innmind\Http\Message\ServerRequest;
use Innmind\Specification\SpecificationInterface;
use Innmind\Immutable\SetInterface;

interface ListBuilder
{
    /**
     * @param SetInterface<IdentityInterface> $identities
     * @param ServerRequest $request
     * @param HttpResource $definition
     * @param SpecificationInterface $specification
     * @param Range $range
     *
     * @return SetInterface<Header>
     */
    public function __invoke(
        SetInterface $identities,
        ServerRequest $request,
        HttpResource $definition,
        SpecificationInterface $specification = null,
        Range $range = null
    ): SetInterface;
}
