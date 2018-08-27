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
    Message\StatusCode\StatusCode,
    Message\ReasonPhrase\ReasonPhrase,
    Headers\Headers,
};
use Innmind\Immutable\MapInterface;

final class Create implements Controller
{
    private $decode;
    private $encode;
    private $gateways;
    private $normalize;
    private $denormalize;
    private $buildHeader;

    public function __construct(
        RequestDecoder $decode,
        Encoder $encode,
        IdentityNormalizer $normalize,
        ResourceDenormalizer $denormalize,
        MapInterface $gateways,
        CreateBuilder $headerBuilder
    ) {
        if (
            (string) $gateways->keyType() !== 'string' ||
            (string) $gateways->valueType() !== Gateway::class
        ) {
            throw new \TypeError(sprintf(
                'Argument 4 must be of type MapInterface<string, %s>',
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
        if (!is_null($identity)) {
            throw new LogicException;
        }

        $create = $this
            ->gateways
            ->get((string) $definition->gateway())
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
            $code = new StatusCode(StatusCode::codes()->get('CREATED')),
            new ReasonPhrase(ReasonPhrase::defaults()->get($code->value())),
            $request->protocolVersion(),
            Headers::of(
                ...($this->buildHeader)($identity, $request, $definition, $resource)
            ),
            ($this->encode)(
                $request,
                ($this->normalize)($identity)
            )
        );
    }
}
