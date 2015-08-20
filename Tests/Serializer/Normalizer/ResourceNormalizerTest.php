<?php

namespace Innmind\Rest\Server\Tests\Serializer\Normalizer;

use Innmind\Rest\Server\Serializer\Normalizer\ResourceNormalizer;
use Innmind\Rest\Server\Resource;
use Innmind\Rest\Server\ResourceBuilder;
use Innmind\Rest\Server\Collection;
use Innmind\Rest\Server\Definition\Resource as Definition;
use Innmind\Rest\Server\Definition\Property;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\EventDispatcher\EventDispatcher;

class ResourceNormalizerTest extends \PHPUnit_Framework_TestCase
{
    protected $n;

    public function setUp()
    {
        $this->n = new ResourceNormalizer(
            new ResourceBuilder(
                PropertyAccess::createPropertyAccessor(),
                new EventDispatcher
            )
        );
    }

    public function testSupportNormalization()
    {
        $this->assertTrue($this->n->supportsNormalization(new Resource));
    }

    public function testDoesntSupportNormalization()
    {
        $this->assertFalse($this->n->supportsNormalization([]));
    }

    public function testNormalize()
    {
        $def = new Definition('foo');
        $def
            ->addProperty(
                (new Property('foo'))
                    ->addAccess('READ')
            )
            ->addProperty(
                (new Property('bar'))
                    ->addAccess('UPDATE')
            );
        $r = new Resource;
        $r
            ->set('foo', '1')
            ->set('bar', '2')
            ->setDefinition($def);

        $this->assertSame(
            ['foo' => '1'],
            $this->n->normalize($r)
        );
    }

    public function testSupportDenormalization()
    {
        $this->assertTrue($this->n->supportsDenormalization([], Resource::class));
    }

    public function testDoesntSupportDenormalization()
    {
        $this->assertFalse($this->n->supportsDenormalization([], 'array'));
    }

    public function testDenormalize()
    {
        $def = new Definition('foo');
        $def
            ->addProperty(new Property('foo'))
            ->addProperty(new Property('bar'));

        $r = $this->n->denormalize(
            [
                'resource' => [
                    'foo' => 1,
                    'bar' => 2,
                ],
            ],
            Resource::class,
            null,
            [
                'definition' => $def,
            ]
        );
        $this->assertInstanceOf(
            Resource::class,
            $r
        );
        $this->assertSame(
            2,
            $r->get('bar')
        );
        $this->assertSame(
            1,
            $r->get('foo')
        );

        $resources = $this->n->denormalize(
            [
                'resources' => [[
                    'foo' => 1,
                    'bar' => 2,
                ]],
            ],
            Resource::class,
            null,
            [
                'definition' => $def,
            ]
        );
        $this->assertInstanceOf(
            Collection::class,
            $resources
        );
        $this->assertSame(
            1,
            $resources->count()
        );
        $this->assertSame(
            2,
            $resources->current()->get('bar')
        );
        $this->assertSame(
            1,
            $resources->current()->get('foo')
        );
    }

    /**
     * @expectedException Symfony\Component\Serializer\Exception\LogicException
     * @expectedExceptionMessage You need to specify a resource definition in the denormalization context
     */
    public function testThrowWhenNoDefinitionInContext()
    {
        $this->n->denormalize(
            [
                'foo' => 1,
                'bar' => 2,
            ],
            Resource::class,
            null,
            [
                'access' => 'UPDATE',
            ]
        );
    }

    /**
     * @expectedException Symfony\Component\Serializer\Exception\LogicException
     * @expectedExceptionMessage You need to specify a resource definition in the denormalization context
     */
    public function testThrowWhenInvalidDefinitionInContext()
    {
        $this->n->denormalize(
            [
                'foo' => 1,
                'bar' => 2,
            ],
            Resource::class,
            null,
            [
                'definition' => 'foo',
                'access' => 'UPDATE',
            ]
        );
    }

    /**
     * @expectedException Symfony\Component\Serializer\Exception\UnsupportedException
     * @expectedExceptionMessage Data must be set under the key "resource" or "resources"
     */
    public function testThrowWhenDenormalizerCantFindData()
    {
        $this->n->denormalize(
            [
                'foo' => 1,
                'bar' => 2,
            ],
            Resource::class,
            null,
            [
                'definition' => new Definition('foo'),
            ]
        );
    }
}
