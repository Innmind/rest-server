<?php

namespace Innmind\Rest\Server\Event;

use Innmind\Rest\Server\Definition\Resource as ResourceDefinition;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Request;

class RequestEvent extends Event
{
    protected $definition;
    protected $request;
    protected $action;

    /**
     * @param ResourceDefinition $definition
     * @param Request $request
     * @param string $action Can be either index, get, create, update, delete or options
     */
    public function __construct(
        ResourceDefinition $definition,
        Request $request,
        $action
    ) {
        $this->definition = $definition;
        $this->request = $request;
        $this->action = (string) $action;
    }

    /**
     * Return the definition for the type of resource being handled
     *
     * @return ResourceDefinition
     */
    public function getDefinition()
    {
        return $this->definition;
    }

    /**
     * Return the request made by the client
     *
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Return the action key
     *
     * Can be either: index, get, create, update, delete or options
     *
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }
}
