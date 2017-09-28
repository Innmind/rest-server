<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server;

use Innmind\Rest\Server\{
    Definition\HttpResource as ResourceDefinition,
    Request\Range
};
use Innmind\Specification\SpecificationInterface;
use Innmind\Immutable\SetInterface;

interface ResourceListAccessor
{
    /**
     * @return SetInterface<IdentityInterface>
     */
    public function __invoke(
        ResourceDefinition $definition,
        SpecificationInterface $specification = null,
        Range $range = null
    ): SetInterface;
}
