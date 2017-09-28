<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Gateway;

use Innmind\Rest\Server\{
    Gateway\Gateway,
    Gateway as GatewayInterface,
    ResourceListAccessor,
    ResourceAccessor,
    ResourceCreator,
    ResourceUpdater,
    ResourceRemover,
    ResourceLinker,
    ResourceUnlinker
};
use PHPUnit\Framework\TestCase;

class GatewayTest extends TestCase
{
    public function testInterface()
    {
        $g = new Gateway(
            $la = $this->createMock(ResourceListAccessor::class),
            $a = $this->createMock(ResourceAccessor::class),
            $c = $this->createMock(ResourceCreator::class),
            $u = $this->createMock(ResourceUpdater::class),
            $r = $this->createMock(ResourceRemover::class),
            $l = $this->createMock(ResourceLinker::class),
            $ul = $this->createMock(ResourceUnlinker::class)
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
