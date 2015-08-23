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
        $data = [
            'foo' => 'bar',
            'inlineSub' => ['foo' => 'bar'],
            'inlineSubCollection' => [
                ['foo' => 'bar'],
            ]
        ];
        $expected = $data;

        $this->assertSame(
            json_encode($expected),
            $this->e->encode($data, 'json')
        );
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
