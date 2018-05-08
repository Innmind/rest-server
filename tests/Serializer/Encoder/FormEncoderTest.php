<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Serializer\Encoder;

use Innmind\Rest\Server\Serializer\Encoder\FormEncoder;
use Innmind\Http\{
    Message\ServerRequest\ServerRequest,
    Message\Method,
    ProtocolVersion,
    Headers,
    Message\Environment,
    Message\Cookies,
    Message\Query,
    Message\Form\Form,
    Message\Form\Parameter\Parameter,
    Message\Form\Parameter as ParameterInterface,
    Message\Files,
};
use Innmind\Url\UrlInterface;
use Innmind\Stream\Readable;
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
                $this->createMock(Method::class),
                $this->createMock(ProtocolVersion::class),
                $this->createMock(Headers::class),
                $this->createMock(Readable::class),
                $this->createMock(Environment::class),
                $this->createMock(Cookies::class),
                $this->createMock(Query::class),
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
                $this->createMock(Files::class)
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
