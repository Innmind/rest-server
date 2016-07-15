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
    Message\ServerRequest,
    Message\MethodInterface,
    Message\EnvironmentInterface,
    Message\CookiesInterface,
    Message\FormInterface,
    Message\QueryInterface,
    Message\FilesInterface,
    HeadersInterface,
    Header\HeaderInterface,
    ProtocolVersionInterface
};
use Innmind\Url\UrlInterface;
use Innmind\Filesystem\StreamInterface;
use Innmind\Immutable\{
    Map,
    Set
};

class ContentTypeVerifierTest extends \PHPUnit_Framework_TestCase
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
     * @expectedException Innmind\Http\Exception\Http\UnsupportedMediaTypeException
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
        $headers = $this->createMock(HeadersInterface::class);
        $headers
            ->method('get')
            ->willReturn(
                $header = $this->createMock(HeaderInterface::class)
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
            $this->createMock(MethodInterface::class),
            $this->createMock(ProtocolVersionInterface::class),
            $headers,
            $this->createMock(StreamInterface::class),
            $this->createMock(EnvironmentInterface::class),
            $this->createMock(CookiesInterface::class),
            $this->createMock(QueryInterface::class),
            $this->createMock(FormInterface::class),
            $this->createMock(FilesInterface::class)
        );

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
        $headers = $this->createMock(HeadersInterface::class);
        $headers
            ->method('get')
            ->willReturn(
                $header = $this->createMock(HeaderInterface::class)
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
            $this->createMock(MethodInterface::class),
            $this->createMock(ProtocolVersionInterface::class),
            $headers,
            $this->createMock(StreamInterface::class),
            $this->createMock(EnvironmentInterface::class),
            $this->createMock(CookiesInterface::class),
            $this->createMock(QueryInterface::class),
            $this->createMock(FormInterface::class),
            $this->createMock(FilesInterface::class)
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
        $headers = $this->createMock(HeadersInterface::class);
        $headers
            ->method('has')
            ->willReturn(false);
        $request = new ServerRequest(
            $this->createMock(UrlInterface::class),
            $this->createMock(MethodInterface::class),
            $this->createMock(ProtocolVersionInterface::class),
            $headers,
            $this->createMock(StreamInterface::class),
            $this->createMock(EnvironmentInterface::class),
            $this->createMock(CookiesInterface::class),
            $this->createMock(QueryInterface::class),
            $this->createMock(FormInterface::class),
            $this->createMock(FilesInterface::class)
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
