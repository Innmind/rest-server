<?php

namespace Innmind\Rest\Server\Tests\Serializer\Normalizer;

use Innmind\Rest\Server\Serializer\Normalizer\ResourceNormalizer;
use Innmind\Rest\Server\Resource;
use Innmind\Rest\Server\HttpResourceInterface;
use Innmind\Rest\Server\ResourceBuilder;
use Innmind\Rest\Server\Collection;
use Innmind\Rest\Server\Definition\ResourceDefinition;
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
        $subDef = new ResourceDefinition('bar');
        $subDef->addProperty(
            (new Property('foo'))
                ->setType('string')
                ->addAccess('READ')
        );
        $def = new ResourceDefinition('foo');
        $def
            ->addProperty(
                (new Property('foo'))
                    ->addAccess('READ')
            )
            ->addProperty(
                (new Property('sub'))
                    ->setType('resource')
                    ->addAccess('READ')
                    ->addOption('resource', $subDef)
            )
            ->addProperty(
                (new Property('bar'))
                    ->addAccess('UPDATE')
            );
        $r = new Resource;
        $r
            ->set('foo', '1')
            ->set('bar', '2')
            ->set(
                'sub',
                (new Resource)
                    ->setDefinition($subDef)
                    ->set('foo', 'foo')
            )
            ->setDefinition($def);

        $this->assertSame(
            ['resource' => ['foo' => '1']],
            $this->n->normalize($r)
        );

        $c = new Collection;
        $c[] = $r;

        $this->assertSame(
            ['resources' => [['foo' => '1']]],
            $this->n->normalize($c)
        );

        $def->getProperty('sub')->addOption('inline', null);

        $this->assertSame(
            [
                'resource' => [
                    'foo' => '1',
                    'sub' => [
                        'foo' => 'foo',
                    ],
                ],
            ],
            $this->n->normalize($r)
        );
    }

    public function testSupportDenormalization()
    {
        $this->assertTrue($this->n->supportsDenormalization([], HttpResourceInterface::class));
    }

    public function testDoesntSupportDenormalization()
    {
        $this->assertFalse($this->n->supportsDenormalization([], 'array'));
    }

    public function testDenormalize()
    {
        $def = new ResourceDefinition('foo');
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
            HttpResourceInterface::class,
            null,
            [
                'definition' => $def,
            ]
        );
        $this->assertInstanceOf(
            HttpResourceInterface::class,
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
            HttpResourceInterface::class,
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
            HttpResourceInterface::class,
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
            HttpResourceInterface::class,
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
            HttpResourceInterface::class,
            null,
            [
                'definition' => new ResourceDefinition('foo'),
            ]
        );
    }
}
