<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server;

use Innmind\Rest\Server\Definition\HttpResource as ResourceDefinition;
use Innmind\Specification\SpecificationInterface;
use Innmind\Immutable\SetInterface;

interface ResourceListAccessorInterface
{
    /**
     * @return SetInterface<IdentityInterface>
     */
    public function get(
        ResourceDefinition $definition,
        SpecificationInterface $specification = null
    ): SetInterface;
}
