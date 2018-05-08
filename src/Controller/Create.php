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
    Exception\LogicException,
};
use Innmind\Http\{
    Message\ServerRequest,
    Message\Response,
    Message\StatusCode\StatusCode,
    Message\ReasonPhrase\ReasonPhrase,
    Headers\Headers,
};
use Innmind\Filesystem\Stream\StringStream;
use Innmind\Immutable\MapInterface;
use Symfony\Component\Serializer\SerializerInterface;

final class Create implements Controller
{
    private $gateways;
    private $serializer;
    private $format;
    private $buildHeader;

    public function __construct(
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
                'Argument 3 must be of type MapInterface<string, %s>',
                Gateway::class
            ));
        }

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
            $resource = $this->serializer->deserialize(
                $request,
                Resource::class,
                'request_'.$this->format->contentType($request)->name(),
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
            new Headers(
                ($this->buildHeader)($identity, $request, $definition, $resource)
            ),
            new StringStream(
                $this->serializer->serialize(
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
