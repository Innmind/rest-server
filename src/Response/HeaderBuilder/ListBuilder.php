<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Response\HeaderBuilder;

use Innmind\Rest\Server\{
    Definition\HttpResource,
    Request\Range,
};
use Innmind\Http\Message\ServerRequest;
use Innmind\Specification\Specification;
use Innmind\Immutable\SetInterface;

interface ListBuilder
{
    /**
     * @param SetInterface<IdentityInterface> $identities
     *
     * @return SetInterface<Header>
     */
    public function __invoke(
        SetInterface $identities,
        ServerRequest $request,
        HttpResource $definition,
        Specification $specification = null,
        Range $range = null
    ): SetInterface;
}
