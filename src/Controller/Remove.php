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
    Message\StatusCode,
    Headers
};
use Innmind\Immutable\Map;
use function Innmind\Immutable\unwrap;

final class Remove implements Controller
{
    /** @var Map<string, Gateway> */
    private Map $gateways;
    private RemoveBuilder $buildHeader;

    /**
     * @param Map<string, Gateway> $gateways
     */
    public function __construct(
        Map $gateways,
        RemoveBuilder $headerBuilder
    ) {
        if (
            $gateways->keyType() !== 'string' ||
            $gateways->valueType() !== Gateway::class
        ) {
            throw new \TypeError(\sprintf(
                'Argument 1 must be of type Map<string, %s>',
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
            ->get($definition->gateway()->toString())
            ->resourceRemover();

        /** @psalm-suppress PossiblyNullArgument */
        $remove($definition, $identity);

        /** @psalm-suppress PossiblyNullArgument */
        return new Response\Response(
            $code = StatusCode::of('NO_CONTENT'),
            $code->associatedreasonPhrase(),
            $request->protocolVersion(),
            Headers::of(
                ...unwrap(($this->buildHeader)($request, $definition, $identity))
            )
        );
    }
}
