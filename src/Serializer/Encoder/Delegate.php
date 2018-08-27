<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Serializer\Encoder;

use Innmind\Rest\Server\{
    Serializer\Encoder,
    Format,
};
use Innmind\Http\Message\ServerRequest;
use Innmind\Stream\Readable;
use Innmind\Immutable\MapInterface;

final class Delegate implements Encoder
{
    private $format;
    private $encoders;

    public function __construct(Format $format, MapInterface $encoders)
    {
        if (
            (string) $encoders->keyType() !== 'string' ||
            (string) $encoders->valueType() !== Encoder::class
        ) {
            throw new \TypeError(sprintf(
                'Argument 2 must be of type MapInterface<string, %s>',
                Encoder::class
            ));
        }

        $this->format = $format;
        $this->encoders = $encoders;
    }

    public function __invoke(ServerRequest $request, array $data): Readable
    {
        $encode = $this->encoders->get(
            $this->format->acceptable($request)->name()
        );

        return $encode($request, $data);
    }
}