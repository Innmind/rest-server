<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Response\HeaderBuilder;

use Innmind\Rest\Server\{
    Definition\HttpResource,
    IdentityInterface
};
use Innmind\Http\Message\ServerRequest;
use Innmind\Immutable\MapInterface;

interface RemoveBuilderInterface
{
    /**
     * @param ServerRequest $request
     * @param HttpResource $definition
     * @param IdentityInterface $identity
     *
     * @return MapInterface<string, HeaderInterface>
     */
    public function build(
        ServerRequest $request,
        HttpResource $definition,
        IdentityInterface $identity
    ): MapInterface;
}
