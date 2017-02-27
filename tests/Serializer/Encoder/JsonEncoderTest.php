<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Serializer\Encoder;

use Innmind\Rest\Server\Serializer\Encoder\JsonEncoder;
use Innmind\Http\{
    Message\ServerRequest,
    Message\MethodInterface,
    ProtocolVersionInterface,
    HeadersInterface,
    Message\EnvironmentInterface,
    Message\CookiesInterface,
    Message\QueryInterface,
    Message\FormInterface,
    Message\FilesInterface
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
                $this->createMock(MethodInterface::class),
                $this->createMock(ProtocolVersionInterface::class),
                $this->createMock(HeadersInterface::class),
                new StringStream('{"identity":42}'),
                $this->createMock(EnvironmentInterface::class),
                $this->createMock(CookiesInterface::class),
                $this->createMock(QueryInterface::class),
                $this->createMock(FormInterface::class),
                $this->createMock(FilesInterface::class)
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
