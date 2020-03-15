<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Response\HeaderBuilder;

use Innmind\Rest\Server\{
    Definition\HttpResource,
    Request\Range,
};
use Innmind\Http\Message\ServerRequest;
use Innmind\Specification\Specification;
use Innmind\Immutable\Set;

interface ListBuilder
{
    /**
     * @param Set<IdentityInterface> $identities
     *
     * @return Set<Header>
     */
    public function __invoke(
        Set $identities,
        ServerRequest $request,
        HttpResource $definition,
        Specification $specification = null,
        Range $range = null
    ): Set;
}
