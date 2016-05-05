<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server;

use Innmind\Rest\Server\Definition\HttpResource as ResourceDefinition;

interface ResourceCreatorInterface
{
    public function handle(
        ResourceDefinition $definition,
        HttpResourceInterface $resource
    ): IdentityInterface;
}
