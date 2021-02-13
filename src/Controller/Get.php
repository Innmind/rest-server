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
    Message\StatusCode,
    Headers,
};
use Innmind\Immutable\Map;
use function Innmind\Immutable\unwrap;

final class Get implements Controller
{
    private Encoder $encode;
    private ResourceNormalizer $normalize;
    /** @var Map<string, Gateway> */
    private Map $gateways;
    private GetBuilder $buildHeader;

    /**
     * @param Map<string, Gateway> $gateways
     */
    public function __construct(
        Encoder $encode,
        ResourceNormalizer $normalize,
        Map $gateways,
        GetBuilder $headerBuilder
    ) {
        if (
            $gateways->keyType() !== 'string' ||
            $gateways->valueType() !== Gateway::class
        ) {
            throw new \TypeError(\sprintf(
                'Argument 3 must be of type Map<string, %s>',
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
            ->get($definition->gateway()->toString())
            ->resourceAccessor();
        /** @psalm-suppress PossiblyNullArgument */
        $resource = $access($definition, $identity);

        return new Response\Response(
            $code = StatusCode::of('OK'),
            $code->associatedreasonPhrase(),
            $request->protocolVersion(),
            Headers::of(
                ...unwrap(($this->buildHeader)($resource, $request, $definition, $identity))
            ),
            ($this->encode)(
                $request,
                ($this->normalize)($resource)
            )
        );
    }
}
