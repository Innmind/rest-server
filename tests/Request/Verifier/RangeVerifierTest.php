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
    Message\Method,
    Headers,
    Header\Range,
    ProtocolVersion,
    Exception\Http\PreconditionFailed,
};
use Innmind\Url\Url;
use Innmind\Immutable\Set;
use PHPUnit\Framework\TestCase;

class RangeVerifierTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(Verifier::class, new RangeVerifier);
    }

    public function testThrowWhenUsingRangeOnNonGETRequest()
    {
        $verify = new RangeVerifier;
        $headers = Headers::of(
            Range::of('resource', 0, 1),
        );
        $request = new ServerRequest(
            Url::of('http://example.com'),
            Method::post(),
            new ProtocolVersion(2, 0),
            $headers
        );

        $this->expectException(PreconditionFailed::class);

        $verify(
            $request,
            HttpResource::rangeable(
                'foo',
                new Gateway('command'),
                new Identity('uuid'),
                Set::of(Property::class)
            )
        );
    }

    public function testThrowWhenUsingRangeOnNonRageableResource()
    {
        $verify = new RangeVerifier;
        $headers = Headers::of(
            Range::of('resource', 0, 1),
        );
        $request = new ServerRequest(
            Url::of('http://example.com'),
            Method::get(),
            new ProtocolVersion(2, 0),
            $headers
        );

        $this->expectException(PreconditionFailed::class);

        $verify(
            $request,
            new HttpResource(
                'foo',
                new Gateway('command'),
                new Identity('uuid'),
                Set::of(Property::class)
            )
        );
    }

    public function testVerify()
    {
        $verify = new RangeVerifier;
        $headers = Headers::of(
            Range::of('resource', 0, 1),
        );
        $request = new ServerRequest(
            Url::of('http://example.com'),
            Method::get(),
            new ProtocolVersion(2, 0),
            $headers
        );

        $this->assertNull(
            $verify(
                $request,
                HttpResource::rangeable(
                    'foo',
                    new Gateway('command'),
                    new Identity('uuid'),
                    Set::of(Property::class)
                )
            )
        );

        $headers = Headers::of();
        $request = new ServerRequest(
            Url::of('http://example.com'),
            Method::get(),
            new ProtocolVersion(2, 0),
            $headers
        );

        $this->assertNull(
            $verify(
                $request,
                new HttpResource(
                    'foo',
                    new Gateway('command'),
                    new Identity('uuid'),
                    Set::of(Property::class)
                )
            )
        );
    }
}
