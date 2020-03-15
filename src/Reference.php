<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server;

use Innmind\Rest\Server\Definition\HttpResource as ResourceDefinition;

final class Reference
{
    private ResourceDefinition $definition;
    private Identity $identity;

    public function __construct(
        ResourceDefinition $definition,
        Identity $identity
    ) {
        $this->definition = $definition;
        $this->identity = $identity;
    }

    public function definition(): ResourceDefinition
    {
        return $this->definition;
    }

    public function identity(): Identity
    {
        return $this->identity;
    }
}
