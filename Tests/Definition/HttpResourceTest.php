<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Tests\Definition;

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
            $i = new Identity('foo'),
            $p = (new Map('string', Property::class)),
            $o = new Collection([]),
            $m = new Collection([]),
            $g = new Gateway('bar')
        );

        $this->assertSame($i, $r->identity());
        $this->assertSame($p, $r->properties());
        $this->assertSame($o, $r->options());
        $this->assertSame($m, $r->metas());
        $this->assertSame($g, $r->gateway());
    }

    /**
     * @expectedException Innmind\Rest\Server\Exception\InvalidArgumentException
     */
    public function testThrowForInvalidPropertyMap()
    {
        new HttpResource(
            new Identity('foo'),
            new Map('string', 'string'),
            new Collection([]),
            new Collection([]),
            new Gateway('bar')
        );
    }
}
