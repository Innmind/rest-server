<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Definition;

class Identity
{
    private $property;

    public function __construct(string $property)
    {
        $this->property = $property;
    }

    public function property(): string
    {
        return $this->property;
    }

    public function __toString(): string
    {
        return $this->property;
    }
}
