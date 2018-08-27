<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Serializer\Normalizer;

use Innmind\Rest\Server\Identity as IdentityInterface;

final class Identity
{
    public function __invoke(IdentityInterface $identity): array
    {
        return ['identity' => $identity->value()];
    }
}
