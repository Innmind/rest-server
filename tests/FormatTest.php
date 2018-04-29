<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server;

use Innmind\Rest\Server\{
    Format,
    Formats,
    Format\Format as FormatFormat,
    Format\MediaType
};
use Innmind\Http\{
    Message\ServerRequest\ServerRequest,
    Message\Method\Method,
    ProtocolVersion\ProtocolVersion,
    Message\Environment\Environment,
    Message\Cookies\Cookies,
    Message\Query\Query,
    Message\Form\Form,
    Message\Files\Files,
    Headers\Headers,
    Header,
    Header\Accept,
    Header\AcceptValue,
    Header\ContentType,
    Header\ContentTypeValue,
    Header\Value
};
use Innmind\Url\Url;
use Innmind\Filesystem\Stream\StringStream;
use Innmind\Immutable\{
    Map,
    Set
};
use PHPUnit\Framework\TestCase;

class FormatTest extends TestCase
{
    private $format;

    public function setUp()
    {
        $accept = new Formats(
            (new Map('string', FormatFormat::class))
                ->put(
                    'json',
                    new FormatFormat(
                        'json',
                        Set::of(MediaType::class, new MediaType('application/json', 0)),
                        10
                    )
                )
                ->put(
                    'html',
                    new FormatFormat(
                        'html',
                        Set::of(MediaType::class, new MediaType('text/html', 0)),
                        0
                    )
                )
        );
        $contentType = new Formats(
            (new Map('string', FormatFormat::class))->put(
                'json',
                new FormatFormat(
                    'json',
                    Set::of(MediaType::class, new MediaType('application/json', 0)),
                    0
                )
            )
        );

        $this->format = new Format($accept, $contentType);
    }

    public function testAcceptable()
    {
        $format = $this->format->acceptable(
            new ServerRequest(
                Url::fromString('/'),
                new Method('GET'),
                new ProtocolVersion(1, 1),
                new Headers(
                    (new Map('string', Header::class))
                        ->put(
                            'Accept',
                            new Accept(
                                new AcceptValue('application', 'json')
                            )
                        )
                ),
                new StringStream(''),
                new Environment,
                new Cookies,
                new Query,
                new Form,
                new Files
            )
        );

        $this->assertInstanceOf(FormatFormat::class, $format);
        $this->assertSame('json', $format->name());
        $this->assertSame('application/json', (string) $format->preferredMediaType());
    }

    public function testAcceptableWhenAcceptingEverything()
    {
        $format = $this->format->acceptable(
            new ServerRequest(
                Url::fromString('/'),
                new Method('GET'),
                new ProtocolVersion(1, 1),
                new Headers(
                    (new Map('string', Header::class))
                        ->put(
                            'Accept',
                            new Accept(
                                new AcceptValue('*', '*')
                            )
                        )
                ),
                new StringStream(''),
                new Environment,
                new Cookies,
                new Query,
                new Form,
                new Files
            )
        );

        $this->assertInstanceOf(FormatFormat::class, $format);
        $this->assertSame('json', $format->name());
        $this->assertSame('application/json', (string) $format->preferredMediaType());
    }

    public function testContentType()
    {
        $format = $this->format->contentType(
            new ServerRequest(
                Url::fromString('/'),
                new Method('GET'),
                new ProtocolVersion(1, 1),
                new Headers(
                    (new Map('string', Header::class))
                        ->put(
                            'Content-Type',
                            new ContentType(
                                new ContentTypeValue('application', 'json')
                            )
                        )
                ),
                new StringStream(''),
                new Environment,
                new Cookies,
                new Query,
                new Form,
                new Files
            )
        );

        $this->assertInstanceOf(FormatFormat::class, $format);
        $this->assertSame('json', $format->name());
        $this->assertSame('application/json', (string) $format->preferredMediaType());
    }
}
