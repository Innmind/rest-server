<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Definition;

use Innmind\Immutable\{
    SetInterface,
    Set,
};

final class Property
{
    private $name;
    private $type;
    private $access;
    private $variants;
    private $optional;

    private function __construct(
        string $name,
        Type $type,
        Access $access,
        SetInterface $variants,
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
     * @return SetInterface<string>
     */
    public function variants(): SetInterface
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
