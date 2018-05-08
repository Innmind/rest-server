<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server;

use Innmind\Rest\Server\{
    Definition\HttpResource as ResourceDefinition,
    HttpResource\Property,
};
use Innmind\Immutable\MapInterface;

interface HttpResource
{
    public function definition(): ResourceDefinition;
    public function property(string $name): Property;

    /**
     * Check if the wished property is set
     *
     * @param string $name
     *
     * @return bool
     */
    public function has(string $name): bool;

    /**
     * @return MapInterface<string, Property>
     */
    public function properties(): MapInterface;
}
