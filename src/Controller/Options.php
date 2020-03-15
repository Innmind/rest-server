<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Controller;

use Innmind\Rest\Server\{
    Controller,
    Format,
    Definition\HttpResource,
    Identity,
    Serializer\Encoder,
    Serializer\Normalizer\Definition,
    Exception\LogicException,
};
use Innmind\Http\{
    Message\ServerRequest,
    Message\Response,
    Message\StatusCode,
    Headers,
    Header\ContentType,
    Header\ContentTypeValue,
};

final class Options implements Controller
{
    private Encoder $encode;
    private Format $format;
    private Definition $normalize;

    public function __construct(
        Encoder $encode,
        Format $format,
        Definition $normalize
    ) {
        $this->encode = $encode;
        $this->format = $format;
        $this->normalize = $normalize;
    }

    public function __invoke(
        ServerRequest $request,
        HttpResource $definition,
        Identity $identity = null
    ): Response {
        if (!is_null($identity)) {
            throw new LogicException;
        }

        $mediaType = $this
            ->format
            ->acceptable($request)
            ->preferredMediaType();

        return new Response\Response(
            $code = StatusCode::of('OK'),
            $code->associatedreasonPhrase(),
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
                ($this->normalize)($definition)
            )
        );
    }
}
