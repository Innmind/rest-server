<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server;

use Innmind\Rest\Server\{
    Definition\HttpResource as ResourceDefinition,
    Request\Range,
};
use Innmind\Specification\Specification;
use Innmind\Immutable\Set;

interface ResourceListAccessor
{
    /**
     * @return Set<IdentityInterface>
     */
    public function __invoke(
        ResourceDefinition $definition,
        Specification $specification = null,
        Range $range = null
    ): Set;
}
