<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Request\Verifier;

use Innmind\Rest\Server\{
    Request\Verifier\AcceptVerifier,
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
    Header\Accept,
    Header\AcceptValue,
    ProtocolVersion,
    Exception\Http\NotAcceptable,
};
use Innmind\Url\Url;
use Innmind\Immutable\Set;
use PHPUnit\Framework\TestCase;

class AcceptVerifierTest extends TestCase
{
    public function testInterface()
    {
        $verifier = new AcceptVerifier(
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

    public function testThrowWhenHeaderNotAccepted()
    {
        $verify = new AcceptVerifier(
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
        $headers = Headers::of(
            new Accept(
                new AcceptValue('text', 'html')
            )
        );

        $request = new ServerRequest(
            Url::of('http://example.com'),
            Method::get(),
            new ProtocolVersion(2, 0),
            $headers
        );

        $this->expectException(NotAcceptable::class);

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

    public function testDoesntThrowWhenAcceptMediaType()
    {
        $verify = new AcceptVerifier(
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
        $headers = Headers::of(
            new Accept(
                new AcceptValue('application', 'json')
            )
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
    }
}
