<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Tests;

use Innmind\Rest\Server\{
    HttpResource,
    HttpResourceInterface,
    Property,
    Definition\HttpResource as Definition,
    Definition\Identity,
    Definition\Property as PropertyDefinition,
    Definition\Gateway
};
use Innmind\Immutable\{
    Map,
    Collection
};

class HttpResourceTest extends \PHPUnit_Framework_TestCase
{
    public function testInterface()
    {
        $r = new HttpResource(
            $d = new Definition(
                'foobar',
                new Identity('foo'),
                (new Map('string', PropertyDefinition::class)),
                new Collection([]),
                new Collection([]),
                new Gateway('bar')
            ),
            $ps = (new Map('string', Property::class))
                ->put('foo', $p = new Property('foo', 42))
        );

        $this->assertInstanceOf(HttpResourceInterface::class, $r);
        $this->assertSame($d, $r->definition());
        $this->assertTrue($r->has('foo'));
        $this->assertFalse($r->has('bar'));
        $this->assertSame($p, $r->get('foo'));
        $this->assertSame($ps, $r->properties());
    }
}
