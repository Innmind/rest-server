<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server;

use function Innmind\Rest\Server\bootstrap;
use Innmind\Rest\Server\{
    Controller,
    Gateway,
    Routing\Routes,
    Definition\Locator,
};
use Innmind\Immutable\Map;
use PHPUnit\Framework\TestCase;

class BootstrapTest extends TestCase
{
    public function testBootstrap()
    {
        $services = bootstrap(
            Map::of('string', Gateway::class),
            require 'fixtures/mapping.php'
        );

        $this->assertInstanceOf(Routes::class, $services['routes']);
        $this->assertInstanceOf(Controller::class, $services['controller']['create']);
        $this->assertInstanceOf(Controller::class, $services['controller']['get']);
        $this->assertInstanceOf(Controller::class, $services['controller']['index']);
        $this->assertInstanceOf(Controller::class, $services['controller']['options']);
        $this->assertInstanceOf(Controller::class, $services['controller']['remove']);
        $this->assertInstanceOf(Controller::class, $services['controller']['update']);
        $this->assertInstanceOf(Controller::class, $services['controller']['link']);
        $this->assertInstanceOf(Controller::class, $services['controller']['unlink']);
        $this->assertInstanceOf(Controller\Capabilities::class, $services['controller']['capabilities']);
        $this->assertInstanceOf(Locator::class, $services['locator']);
    }
}
