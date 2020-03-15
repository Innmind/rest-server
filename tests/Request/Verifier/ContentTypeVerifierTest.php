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
    Header\ContentType,
    Header\ContentTypeValue,
    ProtocolVersion,
    Exception\Http\UnsupportedMediaType,
};
use Innmind\Url\Url;
use Innmind\Immutable\Set;
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
        $headers = Headers::of(
            new ContentType(
                new ContentTypeValue('text', 'html')
            )
        );
        $request = new ServerRequest(
            Url::of('http://example.com'),
            Method::post(),
            new ProtocolVersion(2, 0),
            $headers
        );

        $this->expectException(UnsupportedMediaType::class);

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
        $headers = Headers::of(
            new ContentType(
                new ContentTypeValue('text', 'html')
            )
        );

        $request = new ServerRequest(
            Url::of('http://example.com'),
            Method::get(),
            new ProtocolVersion(2, 0),
            $headers
        );

        $this->assertNull($verify(
            $request,
            HttpResource::rangeable(
                'foo',
                new Gateway('command'),
                new Identity('uuid'),
                Set::of(Property::class)
            )
        ));
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
        $headers = Headers::of(
            new ContentType(
                new ContentTypeValue('application', 'json')
            )
        );

        $request = new ServerRequest(
            Url::of('http://example.com'),
            Method::post(),
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
