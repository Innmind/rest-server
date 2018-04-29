<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server;

use Innmind\Rest\Server\{
    Formats,
    Format\Format,
    Format\MediaType,
    Controller,
    Gateway
};
use Innmind\Compose\ContainerBuilder\ContainerBuilder;
use Innmind\Url\Path;
use Innmind\Immutable\{
    MapInterface,
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

        $this->assertInstanceOf(MapInterface::class, $container->get('directories'));
        $this->assertInstanceOf(Controller::class, $container->get('create'));
        $this->assertInstanceOf(Controller::class, $container->get('get'));
        $this->assertInstanceOf(Controller::class, $container->get('index'));
        $this->assertInstanceOf(Controller::class, $container->get('options'));
        $this->assertInstanceOf(Controller::class, $container->get('remove'));
        $this->assertInstanceOf(Controller::class, $container->get('update'));
    }
}
