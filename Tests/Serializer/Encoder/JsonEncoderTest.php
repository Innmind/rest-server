<?php

namespace Innmind\Rest\Server\Tests\Serializer\Encoder;

use Innmind\Rest\Server\Serializer\Encoder\JsonEncoder;
use Innmind\Rest\Server\Definition\Resource as Definition;
use Innmind\Rest\Server\Definition\Property;
use Symfony\Component\HttpFoundation\Request;

class JsonEncoderTest extends \PHPUnit_Framework_TestCase
{
    protected $e;

    public function setUp()
    {
        $this->e = new JsonEncoder;
    }

    public function testSupportsDecoding()
    {
        $this->assertTrue($this->e->supportsDecoding('json'));
        $this->assertFalse($this->e->supportsDecoding('form'));
    }

    public function testSupportsEncoding()
    {
        $this->assertTrue($this->e->supportsEncoding('json'));
        $this->assertFalse($this->e->supportsEncoding('form'));
    }

    public function testEncode()
    {
        $definition = new Definition('foo');
        $definition
            ->addProperty(
                (new Property('foo'))
                    ->setType('string')
            )
            ->addProperty(
                (new Property('inlineSub'))
                    ->setType('resource')
                    ->addOption('inline', null)
                    ->addOption('resource', $definition)
            )
            ->addProperty(
                (new Property('inlineSubCollection'))
                    ->setType('array')
                    ->addOption('inner_type', 'resource')
                    ->addOption('resource', $definition)
                    ->addOption('inline', null)
            )
            ->addProperty(
                (new Property('res'))
                    ->setType('resource')
                    ->addOption('resource', $definition)
            );
        $data = [
            'foo' => 'bar',
            'inlineSub' => ['foo' => 'bar'],
            'inlineSubCollection' => [
                ['foo' => 'bar'],
            ],
            'res' => ['foo' => 'bar'],
        ];
        $expected = $data;
        unset($expected['res']);

        $this->assertSame(
            json_encode($expected),
            $this->e->encode($data, 'json', ['definition' => $definition])
        );
    }

    /**
     * @expectedException Symfony\Component\Serializer\Exception\LogicException
     * @expectedExceptionMessage You need to specify a resource definition in the encoding context
     */
    public function testThrowIfNoDefinitionPassedInContext()
    {
        $this->e->encode([], 'json');
    }

    public function testDecode()
    {
        $req = new Request(
            [],
            [],
            [],
            [],
            [],
            [],
            json_encode($data = ['foo' => 'bar'])
        );
        $this->assertSame(
            $data,
            $this->e->decode($req, 'json')
        );
    }

    /**
     * @expectedException Symfony\Component\Serializer\Exception\InvalidArgumentException
     * @expectedExceptionMessage You need to pass the request object in order to decode its content
     */
    public function testThrowIfTryingToDecodeSomethingElseThanRequest()
    {
        $this->e->decode('{"foo":"bar"}', 'json');
    }
}
