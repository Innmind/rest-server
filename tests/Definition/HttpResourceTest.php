<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Definition;

use Innmind\Rest\Server\Definition\{
    HttpResource,
    Identity,
    Gateway,
    Property
};
use Innmind\Immutable\{
    Collection,
    Map
};

class HttpResourceTest extends \PHPUnit_Framework_TestCase
{
    public function testInterface()
    {
        $r = new HttpResource(
            'foobar',
            $i = new Identity('foo'),
            $p = (new Map('string', Property::class)),
            $o = new Collection([]),
            $m = new Collection([]),
            $g = new Gateway('bar'),
            true,
            $l = new Map('string', 'string')
        );

        $this->assertSame('foobar', $r->name());
        $this->assertSame('foobar', (string) $r);
        $this->assertSame($i, $r->identity());
        $this->assertSame($p, $r->properties());
        $this->assertSame($o, $r->options());
        $this->assertSame($m, $r->metas());
        $this->assertSame($g, $r->gateway());
        $this->assertTrue($r->isRangeable());
        $this->assertSame($l, $r->allowedLinks());
    }

    /**
     * @expectedException Innmind\Rest\Server\Exception\InvalidArgumentException
     */
    public function testThrowForInvalidPropertyMap()
    {
        new HttpResource(
            'foobar',
            new Identity('foo'),
            new Map('string', 'string'),
            new Collection([]),
            new Collection([]),
            new Gateway('bar'),
            false,
            new Map('string', 'string')
        );
    }

    /**
     * @expectedException Innmind\Rest\Server\Exception\InvalidArgumentException
     */
    public function testThrowForInvalidLinkMap()
    {
        new HttpResource(
            'foobar',
            new Identity('foo'),
            new Map('string', Property::class),
            new Collection([]),
            new Collection([]),
            new Gateway('bar'),
            false,
            new Map('int', 'int')
        );
    }
}
