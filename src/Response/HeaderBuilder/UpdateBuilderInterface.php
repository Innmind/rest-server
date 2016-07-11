<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Response\HeaderBuilder;

use Innmind\Rest\Server\{
    Definition\HttpResource,
    HttpResourceInterface,
    IdentityInterface
};
use Innmind\Http\Message\ServerRequestInterface;
use Innmind\Immutable\MapInterface;

interface UpdateBuilderInterface
{
    /**
     * @param ServerRequestInterface $request
     * @param HttpResource $definition
     * @param IdentityInterface $identity
     * @param HttpResourceInterface $resource
     *
     * @return MapInterface<string, HeaderInterface>
     */
    public function build(
        ServerRequestInterface $request,
        HttpResource $definition,
        IdentityInterface $identity,
        HttpResourceInterface $resource
    ): MapInterface;
}
