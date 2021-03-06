<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Definition;

final class Identity
{
    private string $property;

    public function __construct(string $property)
    {
        $this->property = $property;
    }

    public function property(): string
    {
        return $this->property;
    }

    public function toString(): string
    {
        return $this->property;
    }
}
