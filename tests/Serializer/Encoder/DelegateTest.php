<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Serializer\Encoder;

use Innmind\Rest\Server\{
    Serializer\Encoder\Delegate,
    Serializer\Encoder,
    Format,
    Formats,
    Format\MediaType,
};
use Innmind\Http\{
    Message\ServerRequest,
    Headers\Headers,
    Header\Accept,
    Header\AcceptValue,
};
use Innmind\Stream\Readable;
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
            Encoder::class,
            new Delegate(
                new Format(
                    Formats::of($json = new Format\Format(
                        'json',
                        Set::of(MediaType::class, new MediaType('application/json', 0)),
                        0
                    )),
                    Formats::of($json)
                ),
                new Map('string', Encoder::class)
            )
        );
    }

    public function testThrowWhenInvalidDecodersMapKey()
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage('Argument 2 must be of type MapInterface<string, Innmind\Rest\Server\Serializer\Encoder>');

        new Delegate(
            new Format(
                Formats::of($json = new Format\Format(
                    'json',
                    Set::of(MediaType::class, new MediaType('application/json', 0)),
                    0
                )),
                Formats::of($json)
            ),
            new Map('int', Encoder::class)
        );
    }

    public function testThrowWhenInvalidDecodersMapValue()
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage('Argument 2 must be of type MapInterface<string, Innmind\Rest\Server\Serializer\Encoder>');

        new Delegate(
            new Format(
                Formats::of($json = new Format\Format(
                    'json',
                    Set::of(MediaType::class, new MediaType('application/json', 0)),
                    0
                )),
                Formats::of($json)
            ),
            new Map('string', 'callable')
        );
    }

    public function testInvokation()
    {
        $encode = new Delegate(
            new Format(
                Formats::of(
                    new Format\Format(
                        'html',
                        Set::of(MediaType::class, new MediaType('text/html', 0)),
                        0
                    ),
                    $json = new Format\Format(
                        'json',
                        Set::of(MediaType::class, new MediaType('application/json', 0)),
                        0
                    )
                ),
                Formats::of(
                    $json
                )
            ),
            (new Map('string', Encoder::class))
                ->put('html', $html = $this->createMock(Encoder::class))
                ->put('json', $json = $this->createMock(Encoder::class))
        );
        $request = $this->createMock(ServerRequest::class);
        $request
            ->expects($this->once())
            ->method('headers')
            ->willReturn(Headers::of(
                new Accept(new AcceptValue('application', 'json'))
            ));
        $html
            ->expects($this->never())
            ->method('__invoke');
        $json
            ->expects($this->once())
            ->method('__invoke')
            ->with($request, ['foo' => 'bar'])
            ->willReturn($expected = $this->createMock(Readable::class));

        $this->assertSame($expected, $encode($request, ['foo' => 'bar']));
    }
}
