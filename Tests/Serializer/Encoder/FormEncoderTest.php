<?php

namespace Innmind\Rest\Server\Tests\Serializer\Encoder;

use Innmind\Rest\Server\Serializer\Encoder\FormEncoder;
use Symfony\Component\HttpFoundation\Request;

class FormEncoderTest extends \PHPUnit_Framework_TestCase
{
    protected $e;

    public function setUp()
    {
        $this->e = new FormEncoder;
    }

    public function testSupportsDecoding()
    {
        $this->assertTrue($this->e->supportsDecoding('form'));
        $this->assertFalse($this->e->supportsDecoding('json'));
    }

    public function testDecode()
    {
        $r = new Request([], [
            'foo' => [
                'bar' => 'baz',
            ],
        ]);
        $this->assertSame(
            ['foo' => ['bar' => 'baz']],
            $this->e->decode($r, 'form')
        );
    }

    /**
     * @expectedException Symfony\Component\Serializer\Exception\InvalidArgumentException
     * @expectedExceptionMessage You need to pass the request object in order to decode its content
     */
    public function testThrowIfNotDecodingTheHttpRequest()
    {
        $this->e->decode('foo', 'form');
    }
}
