<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Tests\RequestVerifier;

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
        $headers = $this->getMock(HeadersInterface::class);
        $headers
            ->method('has')
            ->will($this->returnCallback(function(string $header) {
                return $header === 'Range';
            }));
        $request = new ServerRequest(
            $this->getMock(UrlInterface::class),
            new Method('POST'),
            $this->getMock(ProtocolVersionInterface::class),
            $headers,
            $this->getMock(StreamInterface::class),
            $this->getMock(EnvironmentInterface::class),
            $this->getMock(CookiesInterface::class),
            $this->getMock(QueryInterface::class),
            $this->getMock(FormInterface::class),
            $this->getMock(FilesInterface::class)
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
        $headers = $this->getMock(HeadersInterface::class);
        $headers
            ->method('has')
            ->will($this->returnCallback(function(string $header) {
                return $header === 'Range';
            }));
        $request = new ServerRequest(
            $this->getMock(UrlInterface::class),
            new Method('GET'),
            $this->getMock(ProtocolVersionInterface::class),
            $headers,
            $this->getMock(StreamInterface::class),
            $this->getMock(EnvironmentInterface::class),
            $this->getMock(CookiesInterface::class),
            $this->getMock(QueryInterface::class),
            $this->getMock(FormInterface::class),
            $this->getMock(FilesInterface::class)
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
        $headers = $this->getMock(HeadersInterface::class);
        $headers
            ->method('has')
            ->will($this->returnCallback(function(string $header) {
                return $header === 'Range';
            }));
        $request = new ServerRequest(
            $this->getMock(UrlInterface::class),
            new Method('GET'),
            $this->getMock(ProtocolVersionInterface::class),
            $headers,
            $this->getMock(StreamInterface::class),
            $this->getMock(EnvironmentInterface::class),
            $this->getMock(CookiesInterface::class),
            $this->getMock(QueryInterface::class),
            $this->getMock(FormInterface::class),
            $this->getMock(FilesInterface::class)
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

        $headers = $this->getMock(HeadersInterface::class);
        $headers
            ->method('has')
            ->willReturn(false);
        $request = new ServerRequest(
            $this->getMock(UrlInterface::class),
            new Method('GET'),
            $this->getMock(ProtocolVersionInterface::class),
            $headers,
            $this->getMock(StreamInterface::class),
            $this->getMock(EnvironmentInterface::class),
            $this->getMock(CookiesInterface::class),
            $this->getMock(QueryInterface::class),
            $this->getMock(FormInterface::class),
            $this->getMock(FilesInterface::class)
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
