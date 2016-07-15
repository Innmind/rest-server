<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\ServerBundle\Serializer\Normalizer;

use Innmind\Rest\Server\{
    Serializer\Normalizer\DefinitionNormalizer,
    Definition\Httpresource,
    Definition\Identity,
    Definition\Property,
    Definition\Gateway,
    Definition\Types,
    Definition\Loader\YamlLoader
};
use Innmind\Immutable\{
    Map,
    Set
};
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class DefinitionNormalizerTest extends \PHPUnit_Framework_TestCase
{
    public function testInterface()
    {
        $normalizer = new DefinitionNormalizer;

        $this->assertInstanceOf(NormalizerInterface::class, $normalizer);
    }

    public function testSupportsNormalization()
    {
        $normalizer = new DefinitionNormalizer;

        $this->assertTrue(
            $normalizer->supportsNormalization(
                new Httpresource(
                    'foobar',
                    new Identity('foo'),
                    new Map('string', Property::class),
                    new Map('scalar', 'variable'),
                    new Map('scalar', 'variable'),
                    new Gateway('bar'),
                    true,
                    new Map('string', 'string')
                )
            )
        );
        $this->assertFalse($normalizer->supportsNormalization([]));
    }

    public function testNormalize()
    {
        $normalizer = new DefinitionNormalizer;
        $directories = (new YamlLoader(new Types))->load(
            (new Set('string'))->add(
                'fixtures/mapping.yml'
            )
        );

        $data = $normalizer->normalize(
            $directories->get('top_dir')->definitions()->get('image')
        );

        $this->assertSame(
            [
                'identity' => 'uuid',
                'properties' => [
                    'uuid' => [
                        'type' => 'string',
                        'access' => ['READ'],
                        'variants' => [],
                        'optional' => false,
                    ],
                    'url' => [
                        'type' => 'string',
                        'access' => ['READ', 'CREATE', 'UPDATE'],
                        'variants' => [],
                        'optional' => false,
                    ],
                ],
                'metas' => [],
                'rangeable' => true,
            ],
            $data
        );
    }
}
