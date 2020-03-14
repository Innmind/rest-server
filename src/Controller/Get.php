<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Controller;

use Innmind\Rest\Server\{
    Controller,
    Identity,
    Definition\HttpResource,
    Response\HeaderBuilder\GetBuilder,
    Gateway,
    Serializer\Encoder,
    Serializer\Normalizer\HttpResource as ResourceNormalizer,
};
use Innmind\Http\{
    Message\ServerRequest,
    Message\Response,
    Message\StatusCode\StatusCode,
    Headers\Headers,
};
use Innmind\Immutable\MapInterface;

final class Get implements Controller
{
    private Encoder $encode;
    private ResourceNormalizer $normalize;
    private MapInterface $gateways;
    private GetBuilder $buildHeader;

    public function __construct(
        Encoder $encode,
        ResourceNormalizer $normalize,
        MapInterface $gateways,
        GetBuilder $headerBuilder
    ) {
        if (
            (string) $gateways->keyType() !== 'string' ||
            (string) $gateways->valueType() !== Gateway::class
        ) {
            throw new \TypeError(sprintf(
                'Argument 3 must be of type MapInterface<string, %s>',
                Gateway::class
            ));
        }

        $this->encode = $encode;
        $this->normalize = $normalize;
        $this->gateways = $gateways;
        $this->buildHeader = $headerBuilder;
    }

    public function __invoke(
        ServerRequest $request,
        HttpResource $definition,
        Identity $identity = null
    ): Response {
        $access = $this
            ->gateways
            ->get((string) $definition->gateway())
            ->resourceAccessor();
        $resource = $access($definition, $identity);

        return new Response\Response(
            $code = StatusCode::of('OK'),
            $code->associatedreasonPhrase(),
            $request->protocolVersion(),
            Headers::of(
                ...($this->buildHeader)($resource, $request, $definition, $identity)
            ),
            ($this->encode)(
                $request,
                ($this->normalize)($resource)
            )
        );
    }
}
