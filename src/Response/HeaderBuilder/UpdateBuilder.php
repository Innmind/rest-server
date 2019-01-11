<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Response\HeaderBuilder;

use Innmind\Rest\Server\{
    Definition\HttpResource,
    HttpResource as HttpResourceInterface,
    Identity,
};
use Innmind\Http\Message\ServerRequest;
use Innmind\Immutable\SetInterface;

interface UpdateBuilder
{
    /**
     * @return SetInterface<Header>
     */
    public function __invoke(
        ServerRequest $request,
        HttpResource $definition,
        Identity $identity,
        HttpResourceInterface $resource
    ): SetInterface;
}
