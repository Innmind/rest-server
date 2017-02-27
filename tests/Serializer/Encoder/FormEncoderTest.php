<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Serializer\Encoder;

use Innmind\Rest\Server\Serializer\Encoder\FormEncoder;
use Innmind\Http\{
    Message\ServerRequest,
    Message\MethodInterface,
    ProtocolVersionInterface,
    HeadersInterface,
    Message\EnvironmentInterface,
    Message\CookiesInterface,
    Message\QueryInterface,
    Message\Form,
    Message\Form\Parameter,
    Message\Form\ParameterInterface,
    Message\FilesInterface
};
use Innmind\Url\UrlInterface;
use Innmind\Filesystem\StreamInterface;
use Innmind\Immutable\Map;
use Symfony\Component\Serializer\Encoder\DecoderInterface;
use PHPUnit\Framework\TestCase;

class FormEncoderTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(
            DecoderInterface::class,
            new FormEncoder
        );
    }

    public function testSupportsDecoding()
    {
        $decoder = new FormEncoder;

        $this->assertTrue($decoder->supportsDecoding('request_form'));
        $this->assertFalse($decoder->supportsDecoding('form'));
    }

    public function testDecode()
    {
        $decoder = new FormEncoder;

        $data = $decoder->decode(
            new ServerRequest(
                $this->createMock(UrlInterface::class),
                $this->createMock(MethodInterface::class),
                $this->createMock(ProtocolVersionInterface::class),
                $this->createMock(HeadersInterface::class),
                $this->createMock(StreamInterface::class),
                $this->createMock(EnvironmentInterface::class),
                $this->createMock(CookiesInterface::class),
                $this->createMock(QueryInterface::class),
                new Form(
                    (new Map('scalar', ParameterInterface::class))
                        ->put('foo', new Parameter('foo', 'bar'))
                        ->put(
                            'bar',
                            new Parameter(
                                'bar',
                                (new Map('scalar', ParameterInterface::class))
                                    ->put('foo', new Parameter('foo', 'baz'))
                            )
                        )
                ),
                $this->createMock(FilesInterface::class)
            ),
            'form'
        );

        $this->assertSame(
            ['foo' => 'bar', 'bar' => ['foo' => 'baz']],
            $data
        );
    }

    /**
     * @expectedException Innmind\Rest\Server\Exception\InvalidArgumentException
     */
    public function testThrowWhenDecodingNonRequest()
    {
        (new FormEncoder)->decode('foo=bar', 'form');
    }
}
