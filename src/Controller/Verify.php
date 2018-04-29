<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Controller;

use Innmind\Rest\Server\{
    Controller,
    Identity,
    Definition\HttpResource,
    Request\Verifier\Verifier
};
use Innmind\Http\Message\{
    ServerRequest,
    Response
};

final class Verify implements Controller
{
    private $verify;
    private $controller;

    public function __construct(Verifier $verify, Controller $controller)
    {
        $this->verify = $verify;
        $this->controller = $controller;
    }

    public function __invoke(
        ServerRequest $request,
        HttpResource $definition,
        Identity $identity = null
    ): Response {
        ($this->verify)($request, $definition);

        return ($this->controller)($request, $definition, $identity);
    }
}
