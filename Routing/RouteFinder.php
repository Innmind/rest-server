<?php

namespace Innmind\Rest\Server\Routing;

use Innmind\Rest\Server\Definition\Resource as Definition;

class RouteFinder
{
    /**
     * Find the route name for the given definition and action
     *
     * @param Definition $definition
     * @param string $action
     *
     * @return string|null
     */
    public function find(Definition $definition, $action)
    {
        $actions = ['index', 'get', 'create', 'update', 'delete', 'options'];

        if (!in_array($action, $actions, true)) {
            return;
        }

        return sprintf(
            'innmind_rest_%s_%s_%s',
            $definition->getCollection(),
            $definition,
            $action
        );
    }
}
