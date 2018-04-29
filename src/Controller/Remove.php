<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Controller;

use Innmind\Rest\Server\{
    Controller,
    Gateway,
    Identity,
    Definition\HttpResource,
    Response\HeaderBuilder\RemoveBuilder
};
use Innmind\Http\{
    Message\ServerRequest,
    Message\Response,
    Message\StatusCode\StatusCode,
    Message\ReasonPhrase\ReasonPhrase,
    Headers\Headers
};
use Innmind\Filesystem\Stream\StringStream;
use Innmind\Immutable\MapInterface;

final class Remove implements Controller
{
    private $gateways;
    private $buildHeader;

    public function __construct(
        MapInterface $gateways,
        RemoveBuilder $headerBuilder
    ) {
        if (
            (string) $gateways->keyType() !== 'string' ||
            (string) $gateways->valueType() !== Gateway::class
        ) {
            throw new \TypeError(sprintf(
                'Argument 1 must be of type MapInterface<string, %s>',
                Gateway::class
            ));
        }

        $this->gateways = $gateways;
        $this->buildHeader = $headerBuilder;
    }

    public function __invoke(
        ServerRequest $request,
        HttpResource $definition,
        Identity $identity = null
    ): Response {
        $remove = $this
            ->gateways
            ->get((string) $definition->gateway())
            ->resourceRemover();

        $remove($definition, $identity);

        return new Response\Response(
            $code = new StatusCode(StatusCode::codes()->get('NO_CONTENT')),
            new ReasonPhrase(ReasonPhrase::defaults()->get($code->value())),
            $request->protocolVersion(),
            new Headers(
                ($this->buildHeader)($request, $definition, $identity)
            )
        );
    }
}
