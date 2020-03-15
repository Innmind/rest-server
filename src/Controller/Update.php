<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Controller;

use Innmind\Rest\Server\{
    Controller,
    Format,
    Gateway,
    Identity,
    Definition\HttpResource,
    Definition\Access,
    Response\HeaderBuilder\UpdateBuilder,
    HttpResource\HttpResource as Resource,
    Serializer\RequestDecoder,
    Serializer\Denormalizer\HttpResource as ResourceDenormalizer,
};
use Innmind\Http\{
    Message\ServerRequest,
    Message\Response,
    Message\StatusCode,
    Headers,
};
use Innmind\Immutable\Map;
use function Innmind\Immutable\unwrap;

final class Update implements Controller
{
    private RequestDecoder $decode;
    private Map $gateways;
    private ResourceDenormalizer $denormalize;
    private Format $format;
    private UpdateBuilder $buildHeader;

    public function __construct(
        RequestDecoder $decode,
        Format $format,
        ResourceDenormalizer $denormalize,
        Map $gateways,
        UpdateBuilder $headerBuilder
    ) {
        if (
            (string) $gateways->keyType() !== 'string' ||
            (string) $gateways->valueType() !== Gateway::class
        ) {
            throw new \TypeError(sprintf(
                'Argument 3 must be of type Map<string, %s>',
                Gateway::class
            ));
        }

        $this->decode = $decode;
        $this->gateways = $gateways;
        $this->denormalize = $denormalize;
        $this->format = $format;
        $this->buildHeader = $headerBuilder;
    }

    public function __invoke(
        ServerRequest $request,
        HttpResource $definition,
        Identity $identity = null
    ): Response {
        $update = $this
            ->gateways
            ->get($definition->gateway()->toString())
            ->resourceUpdater();

        $update(
            $definition,
            $identity,
            $resource = ($this->denormalize)(
                ($this->decode)($request),
                $definition,
                new Access(Access::UPDATE)
            )
        );

        return new Response\Response(
            $code = StatusCode::of('NO_CONTENT'),
            $code->associatedreasonPhrase(),
            $request->protocolVersion(),
            Headers::of(
                ...unwrap(($this->buildHeader)($request, $definition, $identity, $resource))
            )
        );
    }
}
