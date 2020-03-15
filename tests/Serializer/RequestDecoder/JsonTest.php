<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Serializer\RequestDecoder;

use Innmind\Rest\Server\Serializer\{
    RequestDecoder\Json,
    RequestDecoder,
};
use Innmind\Http\Message\ServerRequest;
use Innmind\Stream\Readable;
use PHPUnit\Framework\TestCase;

class JsonTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(RequestDecoder::class, new Json);
    }

    public function testInvokation()
    {
        $request = $this->createMock(ServerRequest::class);
        $request
            ->expects($this->once())
            ->method('body')
            ->willReturn($body = $this->createMock(Readable::class));
        $body
            ->expects($this->once())
            ->method('toString')
            ->willReturn('{"foo":"bar"}');
        $data = (new Json)($request);

        $this->assertSame(['foo' => 'bar'], $data);
    }
}
