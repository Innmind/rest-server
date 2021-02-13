<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Response\HeaderBuilder;

use Innmind\Rest\Server\{
    Definition\HttpResource,
    Identity,
};
use Innmind\Http\{
    Message\ServerRequest,
    Header,
};
use Innmind\Immutable\Set;

interface RemoveBuilder
{
    /**
     * @return Set<Header<Header\Value>>
     */
    public function __invoke(
        ServerRequest $request,
        HttpResource $definition,
        Identity $identity
    ): Set;
}
