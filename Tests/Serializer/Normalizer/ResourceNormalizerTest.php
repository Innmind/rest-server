<?php

namespace Innmind\Rest\Server\Tests\Serializer\Normalizer;

use Innmind\Rest\Server\Serializer\Normalizer\ResourceNormalizer;
use Innmind\Rest\Server\Resource;
use Innmind\Rest\Server\Definition\Resource as Definition;
use Innmind\Rest\Server\Definition\Property;

class ResourceNormalizerTest extends \PHPUnit_Framework_TestCase
{
    protected $n;

    public function setUp()
    {
        $this->p = new ResourceNormalizer;
    }

    public function testSupportNormalization()
    {
        $this->assertTrue($this->p->supportsNormalization(new Resource));
    }

    public function testDoesntSupportNormalization()
    {
        $this->assertFalse($this->p->supportsNormalization([]));
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
            $this->p->normalize($r)
        );
    }

    public function testSupportDenormalization()
    {
        $this->assertTrue($this->p->supportsDenormalization([], Resource::class));
    }

    public function testDoesntSupportDenormalization()
    {
        $this->assertFalse($this->p->supportsDenormalization([], 'array'));
    }

    public function testDenormalize()
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

        $r = $this->p->denormalize(
            [
                'foo' => 1,
                'bar' => 2,
            ],
            Resource::class,
            null,
            [
                'definition' => $def,
                'access' => 'UPDATE',
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
        $this->assertFalse($r->has('foo'));
    }

    /**
     * @expectedException Symfony\Component\Serializer\Exception\LogicException
     * @expectedExceptionMessage You need to specify a resource definition in the denormalization context
     */
    public function testThrowWhenNoDefinitionInContext()
    {
        $this->p->denormalize(
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
        $this->p->denormalize(
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
     * @expectedException Symfony\Component\Serializer\Exception\LogicException
     * @expectedExceptionMessage You need to specify either "CREATE" or "UPDATE" access flags in the denormalization context
     */
    public function testThrowWhenNoWhishedAccessInContext()
    {
        $this->p->denormalize(
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

    /**
     * @expectedException Symfony\Component\Serializer\Exception\LogicException
     * @expectedExceptionMessage You need to specify either "CREATE" or "UPDATE" access flags in the denormalization context
     */
    public function testThrowWhenNoInvalidAccessInContext()
    {
        $this->p->denormalize(
            [
                'foo' => 1,
                'bar' => 2,
            ],
            Resource::class,
            null,
            [
                'definition' => new Definition('foo'),
                'access' => 'READ',
            ]
        );
    }
}
