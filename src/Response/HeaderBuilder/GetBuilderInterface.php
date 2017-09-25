<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Response\HeaderBuilder;

use Innmind\Rest\Server\{
    Definition\HttpResource,
    HttpResourceInterface,
    IdentityInterface
};
use Innmind\Http\Message\ServerRequest;
use Innmind\Immutable\MapInterface;

interface GetBuilderInterface
{
    /**
     * @param HttpResourceInterface $resource
     * @param ServerRequest $request
     * @param HttpResource $definition
     * @param IdentityInterface $identity
     *
     * @return MapInterface<string, HeaderInterface>
     */
    public function build(
        HttpResourceInterface $resource,
        ServerRequest $request,
        HttpResource $definition,
        IdentityInterface $identity
    ): MapInterface;
}
