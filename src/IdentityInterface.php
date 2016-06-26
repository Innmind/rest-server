<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server;

/**
 * Represent the identity value of a resource
 */
interface IdentityInterface
{
    /**
     * @return mixed
     */
    public function value();
    public function __toString(): string;
}
