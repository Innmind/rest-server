<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Format;

use Innmind\Rest\Server\Exception\InvalidArgumentException;
use Innmind\Immutable\StringPrimitive as Str;

class MediaType
{
    const PATTERN = '/^[a-z]+\/[a-z.\-_+]+$/';
    private $mime;
    private $priority;

    public function __construct(string $mime, int $priority)
    {
        if (!(new Str($mime))->match(self::PATTERN)) {
            throw new InvalidArgumentException;
        }

        $this->mime = $mime;
        $this->priority = $priority;
    }

    public function mime(): string
    {
        return $this->mime;
    }

    public function priority(): int
    {
        return $this->priority;
    }

    public function __toString(): string
    {
        return $this->mime;
    }
}
