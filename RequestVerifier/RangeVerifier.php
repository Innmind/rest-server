<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\RequestVerifier;

use Innmind\Rest\Server\Definition\HttpResource;
use Innmind\Http\{
    Message\ServerRequestInterface,
    Message\MethodInterface,
    Exception\Http\PreconditionFailedException
};

final class RangeVerifier implements VerifierInterface
{
    /**
     * {@inheritdoc}
     *
     * @throws PreconditionFailedException
     */
    public function verify(
        ServerRequestInterface $request,
        HttpResource $definition
    ) {
        if (
            (string) $request->method() !== MethodInterface::GET &&
            $request->headers()->has('Range')
        ) {
            throw new PreconditionFailedException;
        }

        if (
            !$definition->isRangeable() &&
            $request->headers()->has('Range')
        ) {
            throw new PreconditionFailedException;
        }
    }
}
