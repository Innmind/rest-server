<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server;

/**
 * Represent the identity value of a resource
 */
interface Identity
{
    /**
     * @return scalar
     */
    public function value();
    public function toString(): string;
}
