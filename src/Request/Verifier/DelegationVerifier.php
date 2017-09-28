<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Request\Verifier;

use Innmind\Rest\Server\Definition\HttpResource;
use Innmind\Http\Message\ServerRequest;

final class DelegationVerifier implements Verifier
{
    private $verifiers;

    public function __construct(Verifier ...$verifiers)
    {
        $this->verifiers = $verifiers;
    }

    /**
     * {@inheritdoc}
     */
    public function verify(
        ServerRequest $request,
        HttpResource $definition
    ): void {
        foreach ($this->verifiers as $verifier) {
            $verifier->verify($request, $definition);
        }
    }
}
