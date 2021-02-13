<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Controller;

use Innmind\Rest\Server\{
    Controller,
    Identity,
    Definition\HttpResource,
    Definition\Access,
    Gateway,
    Response\HeaderBuilder\CreateBuilder,
    HttpResource\HttpResource as Resource,
    Serializer\RequestDecoder,
    Serializer\Encoder,
    Serializer\Normalizer\Identity as IdentityNormalizer,
    Serializer\Denormalizer\HttpResource as ResourceDenormalizer,
    Exception\LogicException,
};
use Innmind\Http\{
    Message\ServerRequest,
    Message\Response,
    Message\StatusCode,
    Headers,
};
use Innmind\Immutable\Map;
use function Innmind\Immutable\unwrap;

final class Create implements Controller
{
    private RequestDecoder $decode;
    private Encoder $encode;
    /** @var Map<string, Gateway> */
    private Map $gateways;
    private IdentityNormalizer $normalize;
    private ResourceDenormalizer $denormalize;
    private CreateBuilder $buildHeader;

    /**
     * @param Map<string, Gateway> $gateways
     */
    public function __construct(
        RequestDecoder $decode,
        Encoder $encode,
        IdentityNormalizer $normalize,
        ResourceDenormalizer $denormalize,
        Map $gateways,
        CreateBuilder $headerBuilder
    ) {
        if (
            $gateways->keyType() !== 'string' ||
            $gateways->valueType() !== Gateway::class
        ) {
            throw new \TypeError(\sprintf(
                'Argument 4 must be of type Map<string, %s>',
                Gateway::class
            ));
        }

        $this->decode = $decode;
        $this->encode = $encode;
        $this->gateways = $gateways;
        $this->normalize = $normalize;
        $this->denormalize = $denormalize;
        $this->buildHeader = $headerBuilder;
    }

    public function __invoke(
        ServerRequest $request,
        HttpResource $definition,
        Identity $identity = null
    ): Response {
        if (!\is_null($identity)) {
            throw new LogicException;
        }

        $create = $this
            ->gateways
            ->get($definition->gateway()->toString())
            ->resourceCreator();

        $identity = $create(
            $definition,
            $resource = ($this->denormalize)(
                ($this->decode)($request),
                $definition,
                new Access(Access::CREATE)
            )
        );

        return new Response\Response(
            $code = StatusCode::of('CREATED'),
            $code->associatedreasonPhrase(),
            $request->protocolVersion(),
            Headers::of(
                ...unwrap(($this->buildHeader)($identity, $request, $definition, $resource))
            ),
            ($this->encode)(
                $request,
                ($this->normalize)($identity)
            )
        );
    }
}
