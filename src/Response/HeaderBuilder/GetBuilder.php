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

interface GetBuilder
{
    /**
     * @param HttpResourceInterface $resource
     * @param ServerRequest $request
     * @param HttpResource $definition
     * @param Identity $identity
     *
     * @return SetInterface<Header>
     */
    public function __invoke(
        HttpResourceInterface $resource,
        ServerRequest $request,
        HttpResource $definition,
        Identity $identity
    ): SetInterface;
}
