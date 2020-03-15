<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server;

use Innmind\Rest\Server\{
    Reference,
    Identity as IdentityInterface,
    Definition\HttpResource,
    Definition\Identity,
    Definition\Property,
    Definition\Gateway,
};
use Innmind\Immutable\Set;
use PHPUnit\Framework\TestCase;

class ReferenceTest extends TestCase
{
    public function testInterface()
    {
        $reference = new Reference(
            $definition = HttpResource::rangeable(
                'foobar',
                new Gateway('bar'),
                new Identity('foo'),
                Set::of(Property::class)
            ),
            $identity = $this->createMock(IdentityInterface::class)
        );

        $this->assertSame($definition, $reference->definition());
        $this->assertSame($identity, $reference->identity());
    }
}
