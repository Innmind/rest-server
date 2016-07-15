<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Request\Verifier;

use Innmind\Rest\Server\{
    Definition\HttpResource,
    Exception\InvalidArgumentException
};
use Innmind\Http\Message\ServerRequestInterface;
use Innmind\Immutable\MapInterface;

final class DelegationVerifier implements VerifierInterface
{
    private $verifiers;

    public function __construct(MapInterface $verifiers)
    {
        if (
            (string) $verifiers->keyType() !== 'int' ||
            (string) $verifiers->valueType() !== VerifierInterface::class
        ) {
            throw new InvalidArgumentException;
        }

        $this->verifiers = $verifiers;
    }

    /**
     * {@inheritdoc}
     */
    public function verify(
        ServerRequestInterface $request,
        HttpResource $definition
    ) {
        $this
            ->verifiers
            ->keys()
            ->sort(function(int $a, int $b) {
                return $a < $b;
            })
            ->foreach(function(int $index) use ($request, $definition) {
                $this->verifiers->get($index)->verify($request, $definition);
            });
    }
}
