<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server;

use Innmind\Rest\Server\Definition\HttpResource as ResourceDefinition;

interface ResourceCreator
{
    public function __invoke(
        ResourceDefinition $definition,
        HttpResource $resource
    ): Identity;
}
