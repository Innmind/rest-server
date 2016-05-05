<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Tests\Definition;

use Innmind\Rest\Server\Definition\{
    HttpResource,
    Identity,
    Gateway,
    Property
};
use Innmind\Url\Url;
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
            $u = Url::fromString('/some/resource/')
        );

        $this->assertSame('foobar', $r->name());
        $this->assertSame('foobar', (string) $r);
        $this->assertSame($i, $r->identity());
        $this->assertSame($p, $r->properties());
        $this->assertSame($o, $r->options());
        $this->assertSame($m, $r->metas());
        $this->assertSame($g, $r->gateway());
        $this->assertSame($u, $r->url());
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
            Url::fromString('/')
        );
    }
}
