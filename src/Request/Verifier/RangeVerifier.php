<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Request\Verifier;

use Innmind\Rest\Server\Definition\HttpResource;
use Innmind\Http\{
    Message\ServerRequest,
    Message\Method,
    Exception\Http\PreconditionFailed
};

final class RangeVerifier implements Verifier
{
    /**
     * {@inheritdoc}
     *
     * @throws PreconditionFailed
     */
    public function verify(
        ServerRequest $request,
        HttpResource $definition
    ) {
        if (
            (string) $request->method() !== Method::GET &&
            $request->headers()->has('Range')
        ) {
            throw new PreconditionFailed;
        }

        if (
            !$definition->isRangeable() &&
            $request->headers()->has('Range')
        ) {
            throw new PreconditionFailed;
        }
    }
}
