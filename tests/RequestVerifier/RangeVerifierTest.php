<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\RequestVerifier;

use Innmind\Rest\Server\{
    RequestVerifier\RangeVerifier,
    RequestVerifier\VerifierInterface,
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
    Set,
    Collection
};

class RangeVerifierTest extends \PHPUnit_Framework_TestCase
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
                new Collection([]),
                new Collection([]),
                new Gateway('command'),
                true
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
                new Collection([]),
                new Collection([]),
                new Gateway('command'),
                false
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
                    new Collection([]),
                    new Collection([]),
                    new Gateway('command'),
                    true
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
                    new Collection([]),
                    new Collection([]),
                    new Gateway('command'),
                    false
                )
            )
        );
    }
}