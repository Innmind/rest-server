<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Request\Verifier;

use Innmind\Rest\Server\{
    Request\Verifier\RangeVerifier,
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
    Message\Method\Method,
    Headers,
    ProtocolVersion,
};
use Innmind\Url\UrlInterface;
use Innmind\Immutable\Set;
use PHPUnit\Framework\TestCase;

class RangeVerifierTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(Verifier::class, new RangeVerifier);
    }

    /**
     * @expectedException Innmind\Http\Exception\Http\PreconditionFailed
     */
    public function testThrowWhenUsingRangeOnNonGETRequest()
    {
        $verify = new RangeVerifier;
        $headers = $this->createMock(Headers::class);
        $headers
            ->method('has')
            ->will($this->returnCallback(function(string $header) {
                return $header === 'Range';
            }));
        $request = new ServerRequest(
            $this->createMock(UrlInterface::class),
            new Method('POST'),
            $this->createMock(ProtocolVersion::class),
            $headers
        );

        $verify(
            $request,
            HttpResource::rangeable(
                'foo',
                new Gateway('command'),
                new Identity('uuid'),
                new Set(Property::class)
            )
        );
    }

    /**
     * @expectedException Innmind\Http\Exception\Http\PreconditionFailed
     */
    public function testThrowWhenUsingRangeOnNonRageableResource()
    {
        $verify = new RangeVerifier;
        $headers = $this->createMock(Headers::class);
        $headers
            ->method('has')
            ->will($this->returnCallback(function(string $header) {
                return $header === 'Range';
            }));
        $request = new ServerRequest(
            $this->createMock(UrlInterface::class),
            Method::get(),
            $this->createMock(ProtocolVersion::class),
            $headers
        );

        $verify(
            $request,
            new HttpResource(
                'foo',
                new Gateway('command'),
                new Identity('uuid'),
                new Set(Property::class)
            )
        );
    }

    public function testVerify()
    {
        $verify = new RangeVerifier;
        $headers = $this->createMock(Headers::class);
        $headers
            ->method('has')
            ->will($this->returnCallback(function(string $header) {
                return $header === 'Range';
            }));
        $request = new ServerRequest(
            $this->createMock(UrlInterface::class),
            Method::get(),
            $this->createMock(ProtocolVersion::class),
            $headers
        );

        $this->assertNull(
            $verify(
                $request,
                HttpResource::rangeable(
                    'foo',
                    new Gateway('command'),
                    new Identity('uuid'),
                    new Set(Property::class)
                )
            )
        );

        $headers = $this->createMock(Headers::class);
        $headers
            ->method('has')
            ->willReturn(false);
        $request = new ServerRequest(
            $this->createMock(UrlInterface::class),
            Method::get(),
            $this->createMock(ProtocolVersion::class),
            $headers
        );

        $this->assertNull(
            $verify(
                $request,
                new HttpResource(
                    'foo',
                    new Gateway('command'),
                    new Identity('uuid'),
                    new Set(Property::class)
                )
            )
        );
    }
}
