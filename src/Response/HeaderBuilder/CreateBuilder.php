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

interface CreateBuilder
{
    /**
     * @param Identity $identity
     * @param ServerRequest $request
     * @param HttpResource $definition
     * @param HttpResourceInterface $resource
     *
     * @return SetInterface<Header>
     */
    public function __invoke(
        Identity $identity,
        ServerRequest $request,
        HttpResource $definition,
        HttpResourceInterface $resource
    ): SetInterface;
}
