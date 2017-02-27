<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Request\Verifier;

use Innmind\Rest\Server\{
    Request\Verifier\RangeVerifier,
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
    Message\Method,
    HeadersInterface,
    ProtocolVersionInterface
};
use Innmind\Url\UrlInterface;
use Innmind\Filesystem\StreamInterface;
use Innmind\Immutable\{
    Map,
    Set
};
use PHPUnit\Framework\TestCase;

class RangeVerifierTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(VerifierInterface::class, new RangeVerifier);
    }

    /**
     * @expectedException Innmind\Http\Exception\Http\PreconditionFailedException
     */
    public function testThrowWhenUsingRangeOnNonGETRequest()
    {
        $verifier = new RangeVerifier;
        $headers = $this->createMock(HeadersInterface::class);
        $headers
            ->method('has')
            ->will($this->returnCallback(function(string $header) {
                return $header === 'Range';
            }));
        $request = new ServerRequest(
            $this->createMock(UrlInterface::class),
            new Method('POST'),
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

    /**
     * @expectedException Innmind\Http\Exception\Http\PreconditionFailedException
     */
    public function testThrowWhenUsingRangeOnNonRageableResource()
    {
        $verifier = new RangeVerifier;
        $headers = $this->createMock(HeadersInterface::class);
        $headers
            ->method('has')
            ->will($this->returnCallback(function(string $header) {
                return $header === 'Range';
            }));
        $request = new ServerRequest(
            $this->createMock(UrlInterface::class),
            new Method('GET'),
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
                false,
                new Map('string', 'string')
            )
        );
    }

    public function testVerify()
    {
        $verifier = new RangeVerifier;
        $headers = $this->createMock(HeadersInterface::class);
        $headers
            ->method('has')
            ->will($this->returnCallback(function(string $header) {
                return $header === 'Range';
            }));
        $request = new ServerRequest(
            $this->createMock(UrlInterface::class),
            new Method('GET'),
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

        $headers = $this->createMock(HeadersInterface::class);
        $headers
            ->method('has')
            ->willReturn(false);
        $request = new ServerRequest(
            $this->createMock(UrlInterface::class),
            new Method('GET'),
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
                    false,
                    new Map('string', 'string')
                )
            )
        );
    }
}
