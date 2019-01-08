<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Serializer\Normalizer;

use Innmind\Rest\Server\{
    Serializer\Normalizer\Identities,
    Identity as IdentityInterface,
    Identity\Identity,
};
use Innmind\Immutable\Set;
use PHPUnit\Framework\TestCase;

class IdentitiesTest extends TestCase
{
    public function testNormalize()
    {
        $normalize = new Identities;

        $this->assertSame(
            ['identities' => [42, '24']],
            $normalize(
                Set::of(
                    IdentityInterface::class,
                    new Identity(42),
                    new Identity('24')
                )
            )
        );
    }
}
