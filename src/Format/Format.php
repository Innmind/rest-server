<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Format;

use Innmind\Rest\Server\Exception\DomainException;
use Innmind\Immutable\Set;

final class Format
{
    private string $name;
    /** @var Set<MediaType> */
    private Set $types;
    private int $priority;
    private MediaType $preferredType;

    /**
     * @param Set<MediaType> $types
     */
    public function __construct(string $name, Set $types, int $priority)
    {
        if ($types->type() !== MediaType::class) {
            throw new \TypeError(\sprintf(
                'Argument 2 must be of type Set<%s>',
                MediaType::class
            ));
        }

        if ($types->size() === 0) {
            throw new DomainException;
        }

        $this->name = $name;
        $this->types = $types;
        $this->priority = $priority;
        $this->preferredType = $types
            ->sort(static function(MediaType $a, MediaType $b): int {
                return (int) ($a->priority() < $b->priority());
            })
            ->first();
    }

    public function name(): string
    {
        return $this->name;
    }

    /**
     * @return Set<MediaType>
     */
    public function mediaTypes(): Set
    {
        return $this->types;
    }

    public function preferredMediaType(): MediaType
    {
        return $this->preferredType;
    }

    public function priority(): int
    {
        return $this->priority;
    }

    public function toString(): string
    {
        return $this->name;
    }
}
