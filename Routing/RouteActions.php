<?php

namespace Innmind\Rest\Server\Routing;

class RouteActions
{
    const INDEX = 'index';
    const GET = 'get';
    const CREATE = 'create';
    const OPTIONS = 'options';
    const UPDATE = 'update';
    const DELETE = 'delete';

    /**
     * Return the list of all actions
     *
     * @return array
     */
    public static function all()
    {
        return [
            self::INDEX,
            self::GET,
            self::CREATE,
            self::OPTIONS,
            self::UPDATE,
            self::DELETE,
        ];
    }
}
