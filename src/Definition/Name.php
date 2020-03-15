<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Definition;

use Innmind\Rest\Server\Exception\DomainException;
use Innmind\Immutable\Str;

final class Name
{
    private string $value;

    public function __construct(string $value)
    {
        if (!Str::of($value)->matches('~^[a-zA-Z0-9_.]+$~')) {
            throw new DomainException($value);
        }

        $this->value = $value;
    }

    public function under(self $name): self
    {
        return new self($name->toString().'.'.$this->toString());
    }

    public function toString(): string
    {
        return $this->value;
    }
}
