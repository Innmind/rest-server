<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server;

use Innmind\Immutable\SetInterface;

interface ResponseInterface
{
    /**
     * Return the original request
     *
     * @return RequestInterface
     */
    public function request(): RequestInterface;

    /**
     * Return the resource to send to the user
     *
     * @throws BadMethodCallException If no resource to be sent
     *
     * @return HttpResourceInterface
     */
    public function resource(): HttpResourceInterface

    /**
     * Check if there's a resource to send
     *
     * @return bool
     */
    public function containsResource(): bool;

    /**
     * Links to put in the response sent to the usre
     *
     * @return SetInterface<LinkInterface>
     */
    public function links(): SetInterface;
}
