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
use Innmind\Immutable\Map;

class ReferenceTest extends \PHPUnit_Framework_TestCase
{
    public function testInterface()
    {
        $reference = new Reference(
            $definition = new HttpResource(
                'foobar',
                new Identity('foo'),
                new Map('string', Property::class),
                new Map('scalar', 'variable'),
                new Map('scalar', 'variable'),
                new Gateway('bar'),
                true,
                new Map('string', 'string')
            ),
            $identity = $this->createMock(IdentityInterface::class)
        );

        $this->assertSame($definition, $reference->definition());
        $this->assertSame($identity, $reference->identity());
    }
}
