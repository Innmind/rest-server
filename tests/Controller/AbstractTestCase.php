<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Controller;

use Innmind\Rest\Server\{
    Format,
    Formats,
    Format\Format as FormatFormat,
    Format\MediaType,
    Serializer\Normalizer\DefinitionNormalizer,
    Serializer\Normalizer\IdentitiesNormalizer,
    Serializer\Normalizer\IdentityNormalizer,
    Serializer\Normalizer\HttpResourceNormalizer,
    Serializer\Encoder\JsonEncoder,
    Serializer\Encoder\FormEncoder,
    Definition,
    Definition\Loader\YamlLoader,
    Definition\Types,
    Router,
    Routing\Routes,
};
use Innmind\Immutable\{
    Map,
    Set,
};
use Symfony\Component\Serializer\{
    Serializer,
    Encoder\JsonEncoder as SfJsonEncoder,
};
use PHPUnit\Framework\TestCase;

class AbstractTestCase extends TestCase
{
    protected $format;
    protected $serializer;
    protected $definition;
    protected $router;
    protected $directories;

    public function setUp()
    {
        $this->format = new Format(
            new Formats(
                (new Map('string', FormatFormat::class))->put(
                    'json',
                    new FormatFormat(
                        'json',
                        Set::of(MediaType::class, new MediaType('application/json', 0)),
                        0
                    )
                )
            ),
            new Formats(
                (new Map('string', FormatFormat::class))->put(
                    'json',
                    new FormatFormat(
                        'json',
                        Set::of(MediaType::class, new MediaType('application/json', 0)),
                        0
                    )
                )
            )
        );
        $this->serializer = new Serializer(
            [
                new DefinitionNormalizer,
                new IdentitiesNormalizer,
                new IdentityNormalizer,
                new HttpResourceNormalizer,
            ],
            [
                new JsonEncoder,
                new FormEncoder,
                new SfJsonEncoder,
            ]
        );
        $this->definition = new Definition\HttpResource(
            'foo',
            new Definition\Identity('uuid'),
            (new Map('string', Definition\Property::class))
                ->put(
                    'uuid',
                    new Definition\Property(
                        'uuid',
                        new Definition\Type\StringType,
                        new Definition\Access('READ'),
                        Set::of('string'),
                        false
                    )
                )
                ->put(
                    'url',
                    new Definition\Property(
                        'url',
                        new Definition\Type\StringType,
                        new Definition\Access('READ', 'CREATE', 'UPDATE'),
                        Set::of('string'),
                        false
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
                $this->directories = (new YamlLoader(new Types))('fixtures/mapping.yml')
            )
        );
    }
}
