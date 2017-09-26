<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server;

/**
 * Represent the identity value of a resource
 */
interface Identity
{
    /**
     * @return mixed
     */
    public function value();
    public function __toString(): string;
}
