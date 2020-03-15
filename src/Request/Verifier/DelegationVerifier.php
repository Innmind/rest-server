<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Request\Verifier;

use Innmind\Rest\Server\Definition\HttpResource;
use Innmind\Http\Message\ServerRequest;

final class DelegationVerifier implements Verifier
{
    /** @var list<Verifier> */
    private array $verifiers;

    public function __construct(Verifier ...$verifiers)
    {
        $this->verifiers = $verifiers;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(
        ServerRequest $request,
        HttpResource $definition
    ): void {
        foreach ($this->verifiers as $verify) {
            $verify($request, $definition);
        }
    }
}
