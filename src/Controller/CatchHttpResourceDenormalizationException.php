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
    Message\ReasonPhrase\ReasonPhrase
};

final class CatchHttpResourceDenormalizationException implements Controller
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
        } catch (HttpResourceDenormalizationException $e) {
            return new Response\Response(
                new StatusCode($code = StatusCode::codes()->get('BAD_REQUEST')),
                new ReasonPhrase(ReasonPhrase::defaults()->get($code)),
                $request->protocolVersion(),
                null
                //todo return the errors ie {"messages": ["err1","err2"]}
            );
        }
    }
}
