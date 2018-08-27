<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server;

use Innmind\Rest\Server\Definition\HttpResource;
use Innmind\Http\Message\{
    ServerRequest,
    Response,
};

interface Controller
{
    public function __invoke(
        ServerRequest $request,
        HttpResource $definition,
        Identity $identity = null
    ): Response;
}
