<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Controller;

use Innmind\Rest\Server\{
    Controller,
    Format,
    Definition\HttpResource,
    Identity,
    Serializer\Encoder,
    Exception\LogicException,
};
use Innmind\Http\{
    Message\ServerRequest,
    Message\Response,
    Message\StatusCode\StatusCode,
    Message\ReasonPhrase\ReasonPhrase,
    Headers\Headers,
    Header\ContentType,
    Header\ContentTypeValue,
};
use Symfony\Component\Serializer\SerializerInterface;

final class Options implements Controller
{
    private $format;
    private $serializer;

    public function __construct(
        Encoder $encode,
        Format $format,
        SerializerInterface $serializer
    ) {
        $this->encode = $encode;
        $this->format = $format;
        $this->serializer = $serializer;
    }

    public function __invoke(
        ServerRequest $request,
        HttpResource $definition,
        Identity $identity = null
    ): Response {
        if (!is_null($identity)) {
            throw new LogicException;
        }

        $format = $this->format->acceptable($request);
        $mediaType = $format->preferredMediaType();

        return new Response\Response(
            $code = new StatusCode(StatusCode::codes()->get('OK')),
            new ReasonPhrase(ReasonPhrase::defaults()->get($code->value())),
            $request->protocolVersion(),
            Headers::of(
                new ContentType(
                    new ContentTypeValue(
                        $mediaType->topLevel(),
                        $mediaType->subType()
                    )
                )
            ),
            ($this->encode)(
                $request,
                $this->serializer->normalize(
                    $definition,
                    $format->name()
                )
            )
        );
    }
}
