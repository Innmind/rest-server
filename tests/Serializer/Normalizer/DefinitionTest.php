<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\ServerBundle\Serializer\Normalizer;

use Innmind\Rest\Server\{
    Serializer\Normalizer\Definition,
    Definition\Httpresource,
    Definition\Identity,
    Definition\Property,
    Definition\Gateway,
    Definition\Loader\YamlLoader,
};
use Innmind\Immutable\Map;
use PHPUnit\Framework\TestCase;

class DefinitionTest extends TestCase
{
    public function testNormalize()
    {
        $normalize = new Definition;
        $directory = require 'fixtures/mapping.php';

        $data = $normalize(
            $directory->definition('image')
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
                'linkable_to' => [
                    'alternate' => 'top_dir.image',
                ],
            ],
            $data
        );
    }
}
