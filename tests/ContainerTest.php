<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server;

use Innmind\Rest\Server\{
    Formats,
    Format\Format,
    Format\MediaType,
    Controller,
    Gateway,
    Routing\Routes,
    Definition\Locator,
};
use Innmind\Compose\ContainerBuilder\ContainerBuilder;
use Innmind\Url\Path;
use Innmind\Immutable\{
    Map,
    Set
};
use PHPUnit\Framework\TestCase;

class ContainerTest extends TestCase
{
    public function testBuild()
    {
        $container = (new ContainerBuilder)(
            new Path('container.yml'),
            (new Map('string', 'mixed'))
                ->put('gateways', new Map('string', Gateway::class))
                ->put('files', Set::of('string', 'fixtures/mapping.yml'))
                ->put(
                    'acceptFormats',
                    new Formats(
                        (new Map('string', Format::class))->put(
                            'json',
                            new Format(
                                'json',
                                Set::of(MediaType::class, new MediaType('application/json', 0)),
                                0
                            )
                        )
                    )
                )
                ->put(
                    'contentTypeFormats',
                    new Formats(
                        (new Map('string', Format::class))->put(
                            'json',
                            new Format(
                                'json',
                                Set::of(MediaType::class, new MediaType('application/json', 0)),
                                0
                            )
                        )
                    )
                )
        );

        $this->assertInstanceOf(Routes::class, $container->get('routes'));
        $this->assertInstanceOf(Controller::class, $container->get('create'));
        $this->assertInstanceOf(Controller::class, $container->get('get'));
        $this->assertInstanceOf(Controller::class, $container->get('index'));
        $this->assertInstanceOf(Controller::class, $container->get('options'));
        $this->assertInstanceOf(Controller::class, $container->get('remove'));
        $this->assertInstanceOf(Controller::class, $container->get('update'));
        $this->assertInstanceOf(Controller::class, $container->get('link'));
        $this->assertInstanceOf(Controller::class, $container->get('unlink'));
        $this->assertInstanceOf(Controller\Capabilities::class, $container->get('capabilities'));
        $this->assertInstanceOf(Locator::class, $container->get('locator'));
    }
}
