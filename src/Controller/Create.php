<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Controller;

use Innmind\Rest\Server\{
    Controller,
    Identity,
    Definition\HttpResource,
    Definition\Access,
    Gateway,
    Format,
    Response\HeaderBuilder\CreateBuilder,
    HttpResource\HttpResource as Resource,
    Serializer\RequestDecoder,
    Serializer\Encoder,
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
use Symfony\Component\Serializer\SerializerInterface;

final class Create implements Controller
{
    private $decode;
    private $encode;
    private $gateways;
    private $serializer;
    private $format;
    private $buildHeader;

    public function __construct(
        RequestDecoder $decode,
        Encoder $encode,
        Format $format,
        SerializerInterface $serializer,
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
        $this->serializer = $serializer;
        $this->format = $format;
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
            $resource = $this->serializer->denormalize(
                ($this->decode)($request),
                Resource::class,
                null,
                [
                    'definition' => $definition,
                    'mask' => new Access(Access::CREATE),
                ]
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
                $this->serializer->normalize(
                    $identity,
                    $this->format->acceptable($request)->name(),
                    [
                        'request' => $request,
                        'definition' => $definition,
                    ]
                )
            )
        );
    }
}
