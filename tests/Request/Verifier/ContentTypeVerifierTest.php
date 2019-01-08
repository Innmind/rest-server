<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Request\Verifier;

use Innmind\Rest\Server\{
    Request\Verifier\ContentTypeVerifier,
    Request\Verifier\Verifier,
    Formats,
    Format\Format,
    Format\MediaType,
    Definition\HttpResource,
    Definition\Identity,
    Definition\Gateway,
    Definition\Property,
};
use Innmind\Http\{
    Message\ServerRequest\ServerRequest,
    Message\Method,
    Headers,
    Header,
    ProtocolVersion,
};
use Innmind\Url\UrlInterface;
use Innmind\Immutable\{
    Map,
    Set,
};
use PHPUnit\Framework\TestCase;

class ContentTypeVerifierTest extends TestCase
{
    public function testInterface()
    {
        $verifier = new ContentTypeVerifier(
            Formats::of(
                new Format(
                    'json',
                    Set::of(
                        MediaType::class,
                        new MediaType('application/json', 0)
                    ),
                    0
                )
            )
        );

        $this->assertInstanceOf(Verifier::class, $verifier);
    }

    /**
     * @expectedException Innmind\Http\Exception\Http\UnsupportedMediaType
     */
    public function testThrowWhenHeaderNotAccepted()
    {
        $verify = new ContentTypeVerifier(
            Formats::of(
                new Format(
                    'json',
                    Set::of(
                        MediaType::class,
                        new MediaType('application/json', 0)
                    ),
                    0
                )
            )
        );
        $headers = $this->createMock(Headers::class);
        $headers
            ->method('get')
            ->willReturn(
                $header = $this->createMock(Header::class)
            );
        $headers
            ->method('has')
            ->willReturn(true);
        $header
            ->method('values')
            ->willReturn(
                Set::of('string', 'text/html')
            );
        $request = new ServerRequest(
            $this->createMock(UrlInterface::class),
            $method = $this->createMock(Method::class),
            $this->createMock(ProtocolVersion::class),
            $headers
        );
        $method
            ->expects($this->once())
            ->method('__toString')
            ->willReturn(Method::POST);

        $verify(
            $request,
            new HttpResource(
                'foo',
                new Identity('uuid'),
                new Map('string', Property::class),
                new Map('scalar', 'variable'),
                new Map('scalar', 'variable'),
                new Gateway('command'),
                true,
                new Map('string', 'string')
            )
        );
    }

    public function testDoesntThrowWhenNotPostOrPutMethod()
    {
        $verify = new ContentTypeVerifier(
            Formats::of(
                new Format(
                    'json',
                    Set::of(
                        MediaType::class,
                        new MediaType('application/json', 0)
                    ),
                    0
                )
            )
        );
        $headers = $this->createMock(Headers::class);
        $headers
            ->method('get')
            ->willReturn(
                $header = $this->createMock(Header::class)
            );
        $headers
            ->method('has')
            ->willReturn(true);
        $header
            ->method('values')
            ->willReturn(
                Set::of('string', 'text/html')
            );
        $request = new ServerRequest(
            $this->createMock(UrlInterface::class),
            $method = $this->createMock(Method::class),
            $this->createMock(ProtocolVersion::class),
            $headers
        );
        $method
            ->expects($this->once())
            ->method('__toString')
            ->willReturn(Method::GET);

        $verify(
            $request,
            new HttpResource(
                'foo',
                new Identity('uuid'),
                new Map('string', Property::class),
                new Map('scalar', 'variable'),
                new Map('scalar', 'variable'),
                new Gateway('command'),
                true,
                new Map('string', 'string')
            )
        );
    }

    public function testDoesntThrowWhenAcceptContentType()
    {
        $verify = new ContentTypeVerifier(
            Formats::of(
                new Format(
                    'json',
                    Set::of(
                        MediaType::class,
                        new MediaType('application/json', 0)
                    ),
                    0
                )
            )
        );
        $headers = $this->createMock(Headers::class);
        $headers
            ->method('get')
            ->willReturn(
                $header = $this->createMock(Header::class)
            );
        $headers
            ->method('has')
            ->willReturn(true);
        $header
            ->method('values')
            ->willReturn(
                Set::of('string', 'application/json')
            );
        $request = new ServerRequest(
            $this->createMock(UrlInterface::class),
            $method = $this->createMock(Method::class),
            $this->createMock(ProtocolVersion::class),
            $headers
        );
        $method
            ->expects($this->once())
            ->method('__toString')
            ->willReturn(Method::POST);

        $this->assertNull(
            $verify(
                $request,
                new HttpResource(
                    'foo',
                    new Identity('uuid'),
                    new Map('string', Property::class),
                    new Map('scalar', 'variable'),
                    new Map('scalar', 'variable'),
                    new Gateway('command'),
                    true,
                    new Map('string', 'string')
                )
            )
        );
    }

    public function testDoesntThrowWhenNoContentType()
    {
        $verify = new ContentTypeVerifier(
            Formats::of(
                new Format(
                    'json',
                    Set::of(
                        MediaType::class,
                        new MediaType('application/json', 0)
                    ),
                    0
                )
            )
        );
        $headers = $this->createMock(Headers::class);
        $headers
            ->method('has')
            ->willReturn(false);
        $request = new ServerRequest(
            $this->createMock(UrlInterface::class),
            $this->createMock(Method::class),
            $this->createMock(ProtocolVersion::class),
            $headers
        );

        $this->assertNull(
            $verify(
                $request,
                new HttpResource(
                    'foo',
                    new Identity('uuid'),
                    new Map('string', Property::class),
                    new Map('scalar', 'variable'),
                    new Map('scalar', 'variable'),
                    new Gateway('command'),
                    true,
                    new Map('string', 'string')
                )
            )
        );
    }
}
