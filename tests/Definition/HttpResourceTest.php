<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Definition;

use Innmind\Rest\Server\Definition\{
    HttpResource,
    Identity,
    Gateway,
    Property
};
use Innmind\Immutable\Map;
use PHPUnit\Framework\TestCase;

class HttpResourceTest extends TestCase
{
    public function testInterface()
    {
        $r = new HttpResource(
            'foobar',
            $i = new Identity('foo'),
            $p = (new Map('string', Property::class)),
            $o = new Map('scalar', 'variable'),
            $m = new Map('scalar', 'variable'),
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
            new Map('scalar', 'variable'),
            new Map('scalar', 'variable'),
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
            new Map('scalar', 'variable'),
            new Map('scalar', 'variable'),
            new Gateway('bar'),
            false,
            new Map('int', 'int')
        );
    }

    /**
     * @expectedException Innmind\Rest\Server\Exception\InvalidArgumentException
     */
    public function testThrowForInvalidOptionMap()
    {
        new HttpResource(
            'foobar',
            new Identity('foo'),
            new Map('string', Property::class),
            new Map('string', 'string'),
            new Map('scalar', 'variable'),
            new Gateway('bar'),
            false,
            new Map('string', 'string')
        );
    }

    /**
     * @expectedException Innmind\Rest\Server\Exception\InvalidArgumentException
     */
    public function testThrowForInvalidMetaMap()
    {
        new HttpResource(
            'foobar',
            new Identity('foo'),
            new Map('string', Property::class),
            new Map('scalar', 'variable'),
            new Map('string', 'string'),
            new Gateway('bar'),
            false,
            new Map('string', 'string')
        );
    }
}
