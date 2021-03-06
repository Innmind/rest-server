<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server;

use Innmind\Rest\Server\{
    Definition\HttpResource as ResourceDefinition,
    HttpResource\Property,
};
use Innmind\Immutable\Map;

interface HttpResource
{
    public function definition(): ResourceDefinition;
    public function property(string $name): Property;

    /**
     * Check if the wished property is set
     */
    public function has(string $name): bool;

    /**
     * @return Map<string, Property>
     */
    public function properties(): Map;
}
