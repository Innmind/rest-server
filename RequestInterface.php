<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server;

use Innmind\Rest\Server\Definition\HttpResource as ResourceDefinition;
use Innmind\Immutable\{
    MapInterface,
    SetInterface
};
use Psr\Http\Message\ServerRequestInterface;

interface RequestInterface
{
    /**
     * Return the original http message
     *
     * @return ServerRequestInterface
     */
    public function message(): ServerRequestInterface;

    /**
     * Return the resource definition
     *
     * @return ResourceDefinition
     */
    public function definition(): ResourceDefinition;

    /**
     * Return the http resource contained in the http message
     *
     * @throws BadMethocCallException If the http message doesn't contain a resource
     *
     * @return HttpResourceInterface
     */
    public function resource(): HttpResourceInterface;

    /**
     * Check if a resource has been sent
     *
     * @return bool
     */
    public function containsResource(): bool;

    /**
     * Return the filters to apply when requesting data
     *
     * @return MapInterface<string, Filter>
     */
    public function filters(): MapInterface;

    /**
     * Return the link requested to be created
     *
     * @return SetInterface<InternalLink>
     */
    public function links(): SetInterface;
}
