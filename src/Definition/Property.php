<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Definition;

use Innmind\Immutable\Set;

final class Property
{
    private string $name;
    private Type $type;
    private Access $access;
    private Set $variants;
    private bool $optional;

    private function __construct(
        string $name,
        Type $type,
        Access $access,
        Set $variants,
        bool $optional
    ) {
        $this->name = $name;
        $this->type = $type;
        $this->access = $access;
        $this->variants = $variants;
        $this->optional = $optional;
    }

    public static function required(
        string $name,
        Type $type,
        Access $access,
        string ...$variants
    ): self {
        return new self(
            $name,
            $type,
            $access,
            Set::of('string', ...$variants),
            false
        );
    }

    public static function optional(
        string $name,
        Type $type,
        Access $access,
        string ...$variants
    ): self {
        return new self(
            $name,
            $type,
            $access,
            Set::of('string', ...$variants),
            true
        );
    }

    public function name(): string
    {
        return $this->name;
    }

    public function type(): Type
    {
        return $this->type;
    }

    public function access(): Access
    {
        return $this->access;
    }

    /**
     * @return Set<string>
     */
    public function variants(): Set
    {
        return $this->variants;
    }

    public function isOptional(): bool
    {
        return $this->optional;
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
