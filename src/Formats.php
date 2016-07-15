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
use Negotiation\Negotiator;

final class Formats
{
    private $formats;
    private $negotiator;

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
        $this->negotiator = new Negotiator;
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

    public function fromMediaType(string $wished): Format
    {
        $format = $this
            ->formats
            ->values()
            ->filter(function(Format $format) use ($wished) {
                return $format
                    ->mediaTypes()
                    ->reduce(
                        false,
                        function(bool $carry, MediaType $mediaType) use ($wished): bool {
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

    public function matching(string $wished): Format
    {
        $best = $this->negotiator->getBest(
            $wished,
            $this
                ->mediaTypes()
                ->reduce(
                    [],
                    function(array $carry, MediaType $type): array {
                        $carry[] = (string) $type;

                        return $carry;
                    }
                )
        );

        return $this->best($best->getBasePart().'/'.$best->getSubPart());
    }

    private function best(string $mediaType): Format
    {
        if ($mediaType === '*/*') {
            return $this
                ->formats
                ->values()
                ->sort(function(Format $a, Format $b): bool {
                    return $a->priority() > $b->priority();
                })
                ->first();
        }

        return $this->fromMediaType($mediaType);
    }
}
