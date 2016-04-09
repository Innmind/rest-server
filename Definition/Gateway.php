<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Definition;

class Gateway
{
    private $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
