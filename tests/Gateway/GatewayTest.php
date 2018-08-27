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
    ResourceUnlinker,
};
use PHPUnit\Framework\TestCase;

class GatewayTest extends TestCase
{
    public function testInterface()
    {
        $gateway = new Gateway(
            $list = $this->createMock(ResourceListAccessor::class),
            $accessor = $this->createMock(ResourceAccessor::class),
            $ccreator = $this->createMock(ResourceCreator::class),
            $updater = $this->createMock(ResourceUpdater::class),
            $remover = $this->createMock(ResourceRemover::class),
            $linker = $this->createMock(ResourceLinker::class),
            $unlinker = $this->createMock(ResourceUnlinker::class)
        );

        $this->assertInstanceOf(GatewayInterface::class, $gateway);
        $this->assertSame($list, $gateway->resourceListAccessor());
        $this->assertSame($accessor, $gateway->resourceAccessor());
        $this->assertSame($ccreator, $gateway->resourceCreator());
        $this->assertSame($updater, $gateway->resourceUpdater());
        $this->assertSame($remover, $gateway->resourceRemover());
        $this->assertSame($linker, $gateway->resourceLinker());
        $this->assertSame($unlinker, $gateway->resourceUnlinker());
    }
}
