<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server;

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
            $la = $this->createMock(ResourceListAccessorInterface::class),
            $a = $this->createMock(ResourceAccessorInterface::class),
            $c = $this->createMock(ResourceCreatorInterface::class),
            $u = $this->createMock(ResourceUpdaterInterface::class),
            $r = $this->createMock(ResourceRemoverInterface::class),
            $l = $this->createMock(ResourceLinkerInterface::class),
            $ul = $this->createMock(ResourceUnlinkerInterface::class)
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
