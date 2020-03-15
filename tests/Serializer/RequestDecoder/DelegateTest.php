<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Serializer\RequestDecoder;

use Innmind\Rest\Server\{
    Serializer\RequestDecoder\Delegate,
    Serializer\RequestDecoder,
    Format,
    Formats,
    Format\MediaType,
};
use Innmind\Http\{
    Message\ServerRequest,
    Headers,
    Header\ContentType,
    Header\ContentTypeValue,
};
use Innmind\Immutable\{
    Map,
    Set,
};
use PHPUnit\Framework\TestCase;

class DelegateTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(
            RequestDecoder::class,
            new Delegate(
                new Format(
                    Formats::of($json = new Format\Format(
                        'json',
                        Set::of(MediaType::class, new MediaType('application/json', 0)),
                        0
                    )),
                    Formats::of($json)
                ),
                Map::of('string', RequestDecoder::class)
            )
        );
    }

    public function testThrowWhenInvalidDecodersMapKey()
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage('Argument 2 must be of type Map<string, Innmind\Rest\Server\Serializer\RequestDecoder>');

        new Delegate(
            new Format(
                Formats::of($json = new Format\Format(
                    'json',
                    Set::of(MediaType::class, new MediaType('application/json', 0)),
                    0
                )),
                Formats::of($json)
            ),
            Map::of('int', RequestDecoder::class)
        );
    }

    public function testThrowWhenInvalidDecodersMapValue()
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage('Argument 2 must be of type Map<string, Innmind\Rest\Server\Serializer\RequestDecoder>');

        new Delegate(
            new Format(
                Formats::of($json = new Format\Format(
                    'json',
                    Set::of(MediaType::class, new MediaType('application/json', 0)),
                    0
                )),
                Formats::of($json)
            ),
            Map::of('string', 'callable')
        );
    }

    public function testInvokation()
    {
        $decode = new Delegate(
            new Format(
                Formats::of($json = new Format\Format(
                    'json',
                    Set::of(MediaType::class, new MediaType('application/json', 0)),
                    0
                )),
                Formats::of(
                    new Format\Format(
                        'html',
                        Set::of(MediaType::class, new MediaType('text/html', 0)),
                        0
                    ),
                    $json
                )
            ),
            Map::of('string', RequestDecoder::class)
                ('html', $html = $this->createMock(RequestDecoder::class))
                ('json', $json = $this->createMock(RequestDecoder::class))
        );
        $request = $this->createMock(ServerRequest::class);
        $request
            ->expects($this->once())
            ->method('headers')
            ->willReturn(Headers::of(
                new ContentType(new ContentTypeValue('application', 'json'))
            ));
        $html
            ->expects($this->never())
            ->method('__invoke');
        $json
            ->expects($this->once())
            ->method('__invoke')
            ->with($request)
            ->willReturn(['foo' => 'bar']);

        $this->assertSame(['foo' => 'bar'], $decode($request));
    }
}
