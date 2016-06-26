<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Request;

/**
 * Describe a range of resources to be returned on an index
 */
final class Range
{
    private $firstPosition;
    private $lastPosition;

    public function __construct(int $firstPosition, int $lastPosition)
    {
        $this->firstPosition = $firstPosition;
        $this->lastPosition = $lastPosition;
    }

    public function firstPosition(): int
    {
        return $this->firstPosition;
    }

    public function lastPosition(): int
    {
        return $this->lastPosition;
    }
}
