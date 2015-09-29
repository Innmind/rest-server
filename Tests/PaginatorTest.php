<?php

namespace Innmind\Rest\Server\Tests;

use Innmind\Rest\Server\Paginator;
use Innmind\Rest\Server\Definition\Resource;
use Symfony\Component\HttpFoundation\Request;

class PaginatorTest extends \PHPUnit_Framework_TestCase
{
    protected $p;

    public function setUp()
    {
        $this->p = new Paginator;
    }

    public function testCanPaginate()
    {
        $def = new Resource('foo');
        $r = new Request;
        $this->assertFalse($this->p->canPaginate($r));

        $r->attributes->set('_rest_resource', $def);
        $this->assertFalse($this->p->canPaginate($r));

        $r = new Request(['offset' => '1', 'limit' => '1']);
        $r->attributes->set('_rest_resource', $def);
        $this->assertTrue($this->p->canPaginate($r));

        $r = new Request;
        $r->attributes->set('_rest_resource', $def);
        $def->addOption('paginate', 10);
        $this->assertTrue($this->p->canPaginate($r));
    }

    public function testGetOffset()
    {
        $r = new Request(['offset' => '42']);
        $this->assertSame(42, $this->p->getOffset($r));

        $this->assertSame(0, $this->p->getOffset(new Request));
    }

    public function testGetLimit()
    {
        $def = new Resource('foo');
        $r = new Request(['limit' => '42']);
        $r->attributes->set('_rest_resource', $def);
        $this->assertSame(42, $this->p->getLimit($r));

        $def->addOption('paginate', '24');
        $r = new Request;
        $r->attributes->set('_rest_resource', $def);
        $this->assertSame(24, $this->p->getLimit($r));
    }
}
