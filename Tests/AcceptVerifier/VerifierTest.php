<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Tests\AcceptVerifier;

use Innmind\Rest\Server\{
    AcceptVerifier\Verifier,
    AcceptVerifier\VerifierInterface,
    Formats,
    Format\Format,
    Format\MediaType
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

class VerifierTest extends \PHPUnit_Framework_TestCase
{
    public function testInterface()
    {
        $verifier = new Verifier(
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
     * @expectedException Innmind\Http\Exception\Http\NotAcceptableException
     */
    public function testThrowWhenHeaderNotAcepted()
    {
        $verifier = new Verifier(
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
        $headers = $this->getMock(HeadersInterface::class);
        $headers
            ->method('get')
            ->willReturn(
                $header = $this->getMock(HeaderInterface::class)
            );
        $header
            ->method('values')
            ->willReturn(
                (new Set('string'))->add('text/html')
            );
        $request = new ServerRequest(
            $this->getMock(UrlInterface::class),
            $this->getMock(MethodInterface::class),
            $this->getMock(ProtocolVersionInterface::class),
            $headers,
            $this->getMock(StreamInterface::class),
            $this->getMock(EnvironmentInterface::class),
            $this->getMock(CookiesInterface::class),
            $this->getMock(QueryInterface::class),
            $this->getMock(FormInterface::class),
            $this->getMock(FilesInterface::class)
        );

        $verifier->verify($request);
    }

    public function testDoesntThrowWhenAcceptMediaType()
    {
        $verifier = new Verifier(
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
        $headers = $this->getMock(HeadersInterface::class);
        $headers
            ->method('get')
            ->willReturn(
                $header = $this->getMock(HeaderInterface::class)
            );
        $header
            ->method('values')
            ->willReturn(
                (new Set('string'))->add('application/json')
            );
        $request = new ServerRequest(
            $this->getMock(UrlInterface::class),
            $this->getMock(MethodInterface::class),
            $this->getMock(ProtocolVersionInterface::class),
            $headers,
            $this->getMock(StreamInterface::class),
            $this->getMock(EnvironmentInterface::class),
            $this->getMock(CookiesInterface::class),
            $this->getMock(QueryInterface::class),
            $this->getMock(FormInterface::class),
            $this->getMock(FilesInterface::class)
        );

        $this->assertSame(null, $verifier->verify($request));
    }
}
