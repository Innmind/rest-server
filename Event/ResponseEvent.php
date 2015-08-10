<?php

namespace Innmind\Rest\Server\Event;

use Innmind\Rest\Server\Definition\Resource as ResourceDefinition;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class ResponseEvent extends Event
{
    protected $definition;
    protected $response;
    protected $request;
    protected $content;
    protected $action;

    /**
     * @param ResourceDefinition $definition
     * @param Response $response
     * @param Request $request
     * @param mixed $content Data returned by the request handler
     * @param string $action Can be either index, get, create, update, delete or options
     */
    public function __construct(
        ResourceDefinition $definition,
        Response $response,
        Request $request,
        $content,
        $action
    ) {
        $this->definition = $definition;
        $this->response = $response;
        $this->request = $request;
        $this->content = $content;
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
     * Return the response object that will be sent to the user
     *
     * @return Response
     */
    public function getResponse()
    {
        return $this->response;
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
     * Return the content returned by the request handler
     *
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
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
