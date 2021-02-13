<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Identity;

use Innmind\Rest\Server\Identity as IdentityInterface;

final class Identity implements IdentityInterface
{
    /** @var scalar */
    private $value;
    private string $string;

    /**
     * @param scalar $value
     */
    public function __construct($value)
    {
        $this->value = $value;
        $this->string = (string) $value;
    }

    public function value()
    {
        return $this->value;
    }

    public function toString(): string
    {
        return $this->string;
    }
}
