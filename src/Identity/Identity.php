<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Identity;

use Innmind\Rest\Server\Identity as IdentityInterface;

final class Identity implements IdentityInterface
{
    private $value;
    private $string;

    public function __construct($value)
    {
        $this->value = $value;
        $this->string = (string) $value;
    }

    /**
     * {@inheritdoc}
     */
    public function value()
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->string;
    }
}
