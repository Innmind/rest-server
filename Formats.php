<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server;

use Innmind\Rest\Server\{
    Format\Format,
    Format\MediaType,
    Exception\InvalidArgumentException
};
use Innmind\Immutable\{
    MapInterface,
    SetInterface,
    Set
};

final class Formats
{
    private $formats;

    public function __construct(MapInterface $formats)
    {
        if (
            (string) $formats->keyType() !== 'string' ||
            (string) $formats->valueType() !== Format::class ||
            $formats->size() === 0
        ) {
            throw new InvalidArgumentException;
        }

        $this->formats = $formats;
    }

    public function get(string $name): Format
    {
        return $this->formats->get($name);
    }

    /**
     * @return MapInterface<string, Format>
     */
    public function all(): MapInterface
    {
        return $this->formats;
    }

    /**
     * @return SetInterface<MediaType>
     */
    public function mediaTypes(): SetInterface
    {
        $types = new Set(MediaType::class);
        $this
            ->formats
            ->foreach(function(string $name, Format $format) use (&$types) {
                $types = $types->merge($format->mediaTypes());
            });

        return $types;
    }

    public function formatForMediaType(string $wished): Format
    {
        $format = $this
            ->formats
            ->values()
            ->filter(function (Format $format) use ($wished) {
                return $format
                    ->mediaTypes()
                    ->reduce(
                        false,
                        function (bool $carry, MediaType $mediaType) use ($wished): bool {
                            if ($carry === true) {
                                return true;
                            }

                            return $mediaType->mime() === $wished;
                        }
                    );
            })
            ->current();

        if (!$format instanceof Format) {
            throw new InvalidArgumentException;
        }

        return $format;
    }
}
