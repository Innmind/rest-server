<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Controller;

use Innmind\Rest\Server\{
    Controller,
    Identity,
    Definition\HttpResource,
    Exception\ActionNotImplemented
};
use Innmind\Http\{
    Message\ServerRequest,
    Message\Response,
    Message\StatusCode\StatusCode,
};

final class CatchActionNotImplemented implements Controller
{
    private $controller;

    public function __construct(Controller $controller)
    {
        $this->controller = $controller;
    }

    public function __invoke(
        ServerRequest $request,
        HttpResource $definition,
        Identity $identity = null
    ): Response {
        try {
            return ($this->controller)($request, $definition, $identity);
        } catch (ActionNotImplemented $e) {
            return new Response\Response(
                $code = StatusCode::of('METHOD_NOT_ALLOWED'),
                $code->associatedreasonPhrase(),
                $request->protocolVersion()
            );
        }
    }
}
