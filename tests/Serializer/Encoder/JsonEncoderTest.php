<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Serializer\Encoder;

use Innmind\Rest\Server\Serializer\Encoder\JsonEncoder;
use Innmind\Http\{
    Message\ServerRequest\ServerRequest,
    Message\Method,
    ProtocolVersion,
    Headers,
    Message\Environment,
    Message\Cookies,
    Message\Query,
    Message\Form,
    Message\Files
};
use Innmind\Url\UrlInterface;
use Innmind\Filesystem\Stream\StringStream;
use Symfony\Component\Serializer\Encoder\DecoderInterface;
use PHPUnit\Framework\TestCase;

class JsonEncoderTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(
            DecoderInterface::class,
            new JsonEncoder
        );
    }

    public function testSupportsDecoding()
    {
        $decoder = new JsonEncoder;

        $this->assertTrue($decoder->supportsDecoding('request_json'));
        $this->assertFalse($decoder->supportsDecoding('json'));
    }

    public function testDecode()
    {
        $decoder = new JsonEncoder;

        $data = $decoder->decode(
            new ServerRequest(
                $this->createMock(UrlInterface::class),
                $this->createMock(Method::class),
                $this->createMock(ProtocolVersion::class),
                $this->createMock(Headers::class),
                new StringStream('{"identity":42}'),
                $this->createMock(Environment::class),
                $this->createMock(Cookies::class),
                $this->createMock(Query::class),
                $this->createMock(Form::class),
                $this->createMock(Files::class)
            ),
            'json'
        );

        $this->assertSame(['identity' => 42], $data);
    }

    /**
     * @expectedException Innmind\Rest\Server\Exception\InvalidArgumentException
     */
    public function testThrowWhenDecodingNonRequest()
    {
        (new JsonEncoder)->decode('{"identity":42}', 'json');
    }
}
