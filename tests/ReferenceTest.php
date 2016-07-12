<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server;

use Innmind\Rest\Server\{
    Reference,
    IdentityInterface,
    Definition\HttpResource,
    Definition\Identity,
    Definition\Property,
    Definition\Gateway
};
use Innmind\Immutable\{
    Map,
    Collection
};

class ReferenceTest extends \PHPUnit_Framework_TestCase
{
    public function testInterface()
    {
        $reference = new Reference(
            $definition = new HttpResource(
                'foobar',
                new Identity('foo'),
                new Map('string', Property::class),
                new Collection([]),
                new Collection([]),
                new Gateway('bar'),
                true
            ),
            $identity = $this->createMock(IdentityInterface::class)
        );

        $this->assertSame($definition, $reference->definition());
        $this->assertSame($identity, $reference->identity());
    }
}
