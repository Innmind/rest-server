<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Request\Verifier;

use Innmind\Rest\Server\Definition\HttpResource;
use Innmind\Http\{
    Message\ServerRequest,
    Message\Method,
    Exception\Http\PreconditionFailed,
};

final class RangeVerifier implements Verifier
{
    /**
     * @throws PreconditionFailed
     */
    public function __invoke(
        ServerRequest $request,
        HttpResource $definition
    ): void {
        if (
            $request->method()->toString() !== Method::get()->toString() &&
            $request->headers()->contains('Range')
        ) {
            throw new PreconditionFailed;
        }

        if (
            !$definition->isRangeable() &&
            $request->headers()->contains('Range')
        ) {
            throw new PreconditionFailed;
        }
    }
}
