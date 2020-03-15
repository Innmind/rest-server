<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Serializer\Encoder;

use Innmind\Rest\Server\Serializer\{
    Encoder\Json,
    Encoder,
};
use Innmind\Http\Message\ServerRequest;
use Innmind\Stream\Readable;
use PHPUnit\Framework\TestCase;

class JsonTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(Encoder::class, new Json);
    }

    public function testInvokation()
    {
        $stream = (new Json)(
            $this->createMock(ServerRequest::class),
            ['foo' => 'bar']
        );

        $this->assertInstanceOf(Readable::class, $stream);
        $this->assertSame('{"foo":"bar"}', $stream->toString());
    }
}
