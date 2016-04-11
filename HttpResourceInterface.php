<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server;

use Innmind\Rest\Server\Definition\HttpResource as ResourceDefinition;
use Innmind\Immutable\MapInterface;

interface HttpResourceInterface
{
    public function definition(): ResourceDefinition;
    public function get(string $name): Property;

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
