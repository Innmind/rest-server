<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Response\HeaderBuilder;

use Innmind\Rest\Server\{
    Definition\HttpResource,
    Request\Range,
    Identity,
};
use Innmind\Http\{
    Message\ServerRequest,
    Header,
};
use Innmind\Specification\Specification;
use Innmind\Immutable\Set;

interface ListBuilder
{
    /**
     * @param Set<Identity> $identities
     *
     * @return Set<Header<Header\Value>>
     */
    public function __invoke(
        Set $identities,
        ServerRequest $request,
        HttpResource $definition,
        Specification $specification = null,
        Range $range = null
    ): Set;
}
