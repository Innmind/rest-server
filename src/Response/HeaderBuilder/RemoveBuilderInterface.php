<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Response\HeaderBuilder;

use Innmind\Rest\Server\{
    Definition\HttpResource,
    IdentityInterface
};
use Innmind\Http\Message\ServerRequestInterface;
use Innmind\Immutable\MapInterface;

interface RemoveBuilderInterface
{
    /**
     * @param ServerRequestInterface $request
     * @param HttpResource $definition
     * @param IdentityInterface $identity
     *
     * @return MapInterface<string, HeaderInterface>
     */
    public function build(
        ServerRequestInterface $request,
        HttpResource $definition,
        IdentityInterface $identity
    ): MapInterface;
}
