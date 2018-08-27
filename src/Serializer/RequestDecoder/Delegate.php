<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Serializer\RequestDecoder;

use Innmind\Rest\Server\{
    Serializer\RequestDecoder,
    Format,
};
use Innmind\Http\Message\ServerRequest;
use Innmind\Immutable\MapInterface;

final class Delegate implements RequestDecoder
{
    private $format;
    private $decoders;

    public function __construct(
        Format $format,
        MapInterface $decoders
    ) {
        if (
            (string) $decoders->keyType() !== 'string' ||
            (string) $decoders->valueType() !== RequestDecoder::class
        ) {
            throw new \TypeError(sprintf(
                'Argument 2 must be of type MapInterface<string, %s>',
                RequestDecoder::class
            ));
        }

        $this->format = $format;
        $this->decoders = $decoders;
    }

    public function __invoke(ServerRequest $request): array
    {
        $decode = $this->decoders->get(
            $this->format->contentType($request)->name()
        );

        return $decode($request);
    }
}
