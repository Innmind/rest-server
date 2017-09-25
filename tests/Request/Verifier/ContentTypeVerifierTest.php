<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Request\Verifier;

use Innmind\Rest\Server\{
    Request\Verifier\ContentTypeVerifier,
    Request\Verifier\VerifierInterface,
    Formats,
    Format\Format,
    Format\MediaType,
    Definition\HttpResource,
    Definition\Identity,
    Definition\Gateway,
    Definition\Property
};
use Innmind\Http\{
    Message\ServerRequest\ServerRequest,
    Message\Method,
    Message\Environment,
    Message\Cookies,
    Message\Form,
    Message\Query,
    Message\Files,
    Headers,
    Header,
    ProtocolVersion
};
use Innmind\Url\UrlInterface;
use Innmind\Stream\Readable;
use Innmind\Immutable\{
    Map,
    Set
};
use PHPUnit\Framework\TestCase;

class ContentTypeVerifierTest extends TestCase
{
    public function testInterface()
    {
        $verifier = new ContentTypeVerifier(
            new Formats(
                (new Map('string', Format::class))
                    ->put(
                        'json',
                        new Format(
                            'json',
                            (new Set(MediaType::class))->add(
                                new MediaType('application/json', 0)
                            ),
                            0
                        )
                    )
            )
        );

        $this->assertInstanceOf(VerifierInterface::class, $verifier);
    }

    /**
     * @expectedException Innmind\Http\Exception\Http\UnsupportedMediaType
     */
    public function testThrowWhenHeaderNotAccepted()
    {
        $verifier = new ContentTypeVerifier(
            new Formats(
                (new Map('string', Format::class))
                    ->put(
                        'json',
                        new Format(
                            'json',
                            (new Set(MediaType::class))->add(
                                new MediaType('application/json', 0)
                            ),
                            0
                        )
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
                (new Set('string'))->add('text/html')
            );
        $request = new ServerRequest(
            $this->createMock(UrlInterface::class),
            $method = $this->createMock(Method::class),
            $this->createMock(ProtocolVersion::class),
            $headers,
            $this->createMock(Readable::class),
            $this->createMock(Environment::class),
            $this->createMock(Cookies::class),
            $this->createMock(Query::class),
            $this->createMock(Form::class),
            $this->createMock(Files::class)
        );
        $method
            ->expects($this->once())
            ->method('__toString')
            ->willReturn(Method::POST);

        $verifier->verify(
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
        $verifier = new ContentTypeVerifier(
            new Formats(
                (new Map('string', Format::class))
                    ->put(
                        'json',
                        new Format(
                            'json',
                            (new Set(MediaType::class))->add(
                                new MediaType('application/json', 0)
                            ),
                            0
                        )
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
                (new Set('string'))->add('text/html')
            );
        $request = new ServerRequest(
            $this->createMock(UrlInterface::class),
            $method = $this->createMock(Method::class),
            $this->createMock(ProtocolVersion::class),
            $headers,
            $this->createMock(Readable::class),
            $this->createMock(Environment::class),
            $this->createMock(Cookies::class),
            $this->createMock(Query::class),
            $this->createMock(Form::class),
            $this->createMock(Files::class)
        );
        $method
            ->expects($this->once())
            ->method('__toString')
            ->willReturn(Method::GET);

        $verifier->verify(
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
        $verifier = new ContentTypeVerifier(
            new Formats(
                (new Map('string', Format::class))
                    ->put(
                        'json',
                        new Format(
                            'json',
                            (new Set(MediaType::class))->add(
                                new MediaType('application/json', 0)
                            ),
                            0
                        )
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
                (new Set('string'))->add('application/json')
            );
        $request = new ServerRequest(
            $this->createMock(UrlInterface::class),
            $method = $this->createMock(Method::class),
            $this->createMock(ProtocolVersion::class),
            $headers,
            $this->createMock(Readable::class),
            $this->createMock(Environment::class),
            $this->createMock(Cookies::class),
            $this->createMock(Query::class),
            $this->createMock(Form::class),
            $this->createMock(Files::class)
        );
        $method
            ->expects($this->once())
            ->method('__toString')
            ->willReturn(Method::POST);

        $this->assertSame(
            null,
            $verifier->verify(
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
        $verifier = new ContentTypeVerifier(
            new Formats(
                (new Map('string', Format::class))
                    ->put(
                        'json',
                        new Format(
                            'json',
                            (new Set(MediaType::class))->add(
                                new MediaType('application/json', 0)
                            ),
                            0
                        )
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
            $headers,
            $this->createMock(Readable::class),
            $this->createMock(Environment::class),
            $this->createMock(Cookies::class),
            $this->createMock(Query::class),
            $this->createMock(Form::class),
            $this->createMock(Files::class)
        );

        $this->assertSame(
            null,
            $verifier->verify(
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
