<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Serializer\Normalizer;

use Innmind\Rest\Server\Identity;
use Innmind\Immutable\Set;

final class Identities
{
    /**
     * @param Set<Identity> $identities
     *
     * @return array{identities: list<mixed>}
     */
    public function __invoke(Set $identities): array
    {
        /** @var array{identities: list<mixed>} */
        return [
            'identities' => $identities->reduce(
                [],
                function(array $carry, Identity $identity): array {
                    /** @psalm-suppress MixedAssignment */
                    $carry[] = $identity->value();

                    return $carry;
                },
            ),
        ];
    }
}
