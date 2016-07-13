<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server;

use Innmind\Rest\Server\{
    HttpResource,
    HttpResourceInterface,
    Property,
    Definition\HttpResource as Definition,
    Definition\Identity,
    Definition\Property as PropertyDefinition,
    Definition\Gateway,
    Definition\Type\StringType,
    Definition\Access
};
use Innmind\Immutable\{
    Map,
    Collection,
    Set
};

class HttpResourceTest extends \PHPUnit_Framework_TestCase
{
    public function testInterface()
    {
        $r = new HttpResource(
            $d = new Definition(
                'foobar',
                new Identity('foo'),
                (new Map('string', PropertyDefinition::class))
                    ->put(
                        'foo',
                        new PropertyDefinition(
                            'foo',
                            new StringType,
                            new Access(
                                (new Set('string'))->add(Access::READ)
                            ),
                            new Set('string'),
                            true
                        )
                    ),
                new Collection([]),
                new Collection([]),
                new Gateway('bar'),
                true,
                new Map('string', 'string')
            ),
            $ps = (new Map('string', Property::class))
                ->put('foo', $p = new Property('foo', 42))
        );

        $this->assertInstanceOf(HttpResourceInterface::class, $r);
        $this->assertSame($d, $r->definition());
        $this->assertTrue($r->has('foo'));
        $this->assertFalse($r->has('bar'));
        $this->assertSame($p, $r->property('foo'));
        $this->assertSame($ps, $r->properties());
    }

    /**
     * @expectedException Innmind\Rest\Server\Exception\InvalidArgumentException
     */
    public function testThrowWhenBuildingWithUndefinedProperty()
    {
        new HttpResource(
            $d = new Definition(
                'foobar',
                new Identity('foo'),
                (new Map('string', PropertyDefinition::class)),
                new Collection([]),
                new Collection([]),
                new Gateway('bar'),
                true,
                new Map('string', 'string')
            ),
            $ps = (new Map('string', Property::class))
                ->put('foo', $p = new Property('foo', 42))
        );
    }
}
