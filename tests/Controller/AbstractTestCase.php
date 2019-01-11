<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Controller;

use Innmind\Rest\Server\{
    Format,
    Formats,
    Format\Format as FormatFormat,
    Format\MediaType,
    Definition,
    Router,
    Routing\Routes,
};
use Innmind\Immutable\Set;
use PHPUnit\Framework\TestCase;

class AbstractTestCase extends TestCase
{
    protected $format;
    protected $definition;
    protected $router;
    protected $directory;

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
            new Definition\Gateway('foo'),
            new Definition\Identity('uuid'),
            Set::of(
                Definition\Property::class,
                Definition\Property::required(
                    'uuid',
                    new Definition\Type\StringType,
                    new Definition\Access('READ')
                ),
                Definition\Property::required(
                    'url',
                    new Definition\Type\StringType,
                    new Definition\Access('READ', 'CREATE', 'UPDATE')
                )
            )
        );
        $this->router = new Router(
            Routes::from(
                $this->directory = require 'fixtures/mapping.php'
            )
        );
    }
}
