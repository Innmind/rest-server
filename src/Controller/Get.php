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

final class Get implements Controller
{
    private $encode;
    private $serializer;
    private $gateways;
    private $buildHeader;

    public function __construct(
        Encoder $encode,
        SerializerInterface $serializer,
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
        $this->serializer = $serializer;
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
            $code = new StatusCode(StatusCode::codes()->get('OK')),
            new ReasonPhrase(ReasonPhrase::defaults()->get($code->value())),
            $request->protocolVersion(),
            Headers::of(
                ...($this->buildHeader)($resource, $request, $definition, $identity)
            ),
            ($this->encode)(
                $request,
                $this->serializer->normalize(
                    $resource,
                    null,
                    [
                        'request' => $request,
                        'definition' => $definition,
                        'identity' => $identity,
                    ]
                )
            )
        );
    }
}
