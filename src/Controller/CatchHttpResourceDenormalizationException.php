<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Controller;

use Innmind\Rest\Server\{
    Controller,
    Definition\HttpResource,
    Identity,
    Exception\HttpResourceDenormalizationException
};
use Innmind\Http\{
    Message\ServerRequest,
    Message\Response,
    Message\StatusCode\StatusCode,
};

final class CatchHttpResourceDenormalizationException implements Controller
{
    private Controller $controller;

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
        } catch (HttpResourceDenormalizationException $e) {
            return new Response\Response(
                $code = StatusCode::of('BAD_REQUEST'),
                $code->associatedreasonPhrase(),
                $request->protocolVersion(),
                null
                //todo return the errors ie {"messages": ["err1","err2"]}
            );
        }
    }
}
