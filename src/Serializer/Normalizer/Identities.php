<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Serializer\Normalizer;

use Innmind\Rest\Server\Identity;
use Innmind\Immutable\Set;

final class Identities
{
    /**
     * @param Set<Identity> $identities
     */
    public function __invoke(Set $identities): array
    {
        return $identities->reduce(
            ['identities' => []],
            function(array $carry, Identity $identity): array {
                $carry['identities'][] = $identity->value();

                return $carry;
            }
        );
    }
}
