<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server;

use Innmind\Rest\Server\Definition\HttpResource as ResourceDefinition;

interface ResourceAccessor
{
    public function __invoke(
        ResourceDefinition $definition,
        Identity $identity
    ): HttpResource;
}
