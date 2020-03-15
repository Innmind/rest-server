<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Definition\AllowedLink;

use Innmind\Rest\Server\Exception\DomainException;
use Innmind\Immutable\Str;

final class Parameter
{
    private string $name;

    public function __construct(string $name)
    {
        if (Str::of($name)->empty()) {
            throw new DomainException;
        }

        $this->name = $name;
    }

    public function name(): string
    {
        return $this->name;
    }
}
