<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Tests;

use Innmind\Rest\Server\{
    Gateway,
    GatewayInterface,
    ResourceListAccessorInterface,
    ResourceAccessorInterface,
    ResourceCreatorInterface,
    ResourceUpdaterInterface,
    ResourceRemoverInterface,
    ResourceLinkerInterface,
    ResourceUnlinkerInterface
};

class GatewayTest extends \PHPUnit_Framework_TestCase
{
    public function testInterface()
    {
        $g = new Gateway(
            $la = $this->getMock(ResourceListAccessorInterface::class),
            $a = $this->getMock(ResourceAccessorInterface::class),
            $c = $this->getMock(ResourceCreatorInterface::class),
            $u = $this->getMock(ResourceUpdaterInterface::class),
            $r = $this->getMock(ResourceRemoverInterface::class),
            $l = $this->getMock(ResourceLinkerInterface::class),
            $ul = $this->getMock(ResourceUnlinkerInterface::class)
        );

        $this->assertInstanceOf(GatewayInterface::class, $g);
        $this->assertSame($la, $g->resourceListAccessor());
        $this->assertSame($a, $g->resourceAccessor());
        $this->assertSame($c, $g->resourceCreator());
        $this->assertSame($u, $g->resourceUpdater());
        $this->assertSame($r, $g->resourceRemover());
        $this->assertSame($l, $g->resourceLinker());
        $this->assertSame($ul, $g->resourceUnlinker());
    }
}
