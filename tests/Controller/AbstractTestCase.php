<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Controller;

use Innmind\Rest\Server\{
    Format,
    Formats,
    Format\Format as FormatFormat,
    Format\MediaType,
    Definition,
    Definition\Loader\YamlLoader,
    Router,
    Routing\Routes,
};
use Innmind\Immutable\{
    Map,
    Set,
};
use PHPUnit\Framework\TestCase;

class AbstractTestCase extends TestCase
{
    protected $format;
    protected $definition;
    protected $router;
    protected $directories;

    public function setUp()
    {
        $this->format = new Format(
            Formats::of(
                new FormatFormat(
                    'json',
                    Set::of(MediaType::class, new MediaType('application/json', 0)),
                    0
                )
            ),
            Formats::of(
                new FormatFormat(
                    'json',
                    Set::of(MediaType::class, new MediaType('application/json', 0)),
                    0
                )
            )
        );
        $this->definition = new Definition\HttpResource(
            'foo',
            new Definition\Identity('uuid'),
            Map::of('string', Definition\Property::class)
                (
                    'uuid',
                    Definition\Property::required(
                        'uuid',
                        new Definition\Type\StringType,
                        new Definition\Access('READ')
                    )
                )
                (
                    'url',
                    Definition\Property::required(
                        'url',
                        new Definition\Type\StringType,
                        new Definition\Access('READ', 'CREATE', 'UPDATE')
                    )
                ),
            new Map('scalar', 'variable'),
            new Map('scalar', 'variable'),
            new Definition\Gateway('foo'),
            false,
            new Map('string', 'string')
        );
        $this->router = new Router(
            Routes::from(
                $this->directories = (new YamlLoader)('fixtures/mapping.yml')
            )
        );
    }
}
