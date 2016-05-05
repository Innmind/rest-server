<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server;

use Innmind\Rest\Server\Definition\HttpResource as ResourceDefinition;

interface ResourceAccessorInterface
{
    public function get(
        ResourceDefinition $definition,
        IdentityInterface $identity
    ): HttpResourceInterface;
}
