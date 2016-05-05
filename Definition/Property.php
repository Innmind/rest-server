<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Definition;

use Innmind\Rest\Server\Exception\InvalidArgumentException;
use Innmind\Immutable\SetInterface;

final class Property
{
    private $name;
    private $type;
    private $access;
    private $variants;
    private $optional;

    public function __construct(
        string $name,
        TypeInterface $type,
        Access $access,
        SetInterface $variants,
        bool $optional
    ) {
        if ((string) $variants->type() !== 'string') {
            throw new InvalidArgumentException;
        }

        $this->name = $name;
        $this->type = $type;
        $this->access = $access;
        $this->variants = $variants;
        $this->optional = $optional;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function type(): TypeInterface
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
