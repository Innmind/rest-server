<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Controller;

use Innmind\Rest\Server\{
    Controller,
    Identity,
    Definition\HttpResource
};
use Innmind\Http\{
    Message\ServerRequest,
    Message\Response,
    Message\StatusCode\StatusCode,
    Exception
};

final class CatchHttpException implements Controller
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
        } catch (Exception\Exception $e) {
            if ($e instanceof Exception\Http\Exception) {
                $code = $e->httpCode();
            } else {
                $code = StatusCode::codes()->get('BAD_REQUEST');
            }

            return new Response\Response(
                $code = new StatusCode($code),
                $code->associatedreasonPhrase(),
                $request->protocolVersion()
            );
        }
    }
}
