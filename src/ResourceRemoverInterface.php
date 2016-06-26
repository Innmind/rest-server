<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server;

use Innmind\Rest\Server\Definition\HttpResource as ResourceDefinition;

interface ResourceRemoverInterface
{
    /**
     * @return void
     */
    public function __invoke(
        ResourceDefinition $definition,
        IdentityInterface $identity
    );
}