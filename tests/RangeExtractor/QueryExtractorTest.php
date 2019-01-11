<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\RangeExtractor;

use Innmind\Rest\Server\{
    RangeExtractor\QueryExtractor,
    RangeExtractor\Extractor,
    Request\Range,
    Exception\RangeNotFound,
};
use Innmind\Http\{
    Message\ServerRequest\ServerRequest,
    Message\Method\Method,
    ProtocolVersion\ProtocolVersion,
    Message\Environment\Environment,
    Message\Cookies\Cookies,
    Message\Query\Query,
    Message\Query\Parameter as QueryParameterInterface,
    Message\Query\Parameter\Parameter,
    Headers\Headers,
};
use Innmind\Url\Url;
use Innmind\Filesystem\Stream\StringStream;
use Innmind\Immutable\Map;
use PHPUnit\Framework\TestCase;

class QueryExtractorTest extends TestCase
{
    public function testInterface()
    {
        $extractor = new QueryExtractor;

        $this->assertInstanceOf(Extractor::class, $extractor);
    }

    public function testExtract()
    {
        $extract = new QueryExtractor;
        $request = new ServerRequest(
            Url::fromString('/'),
            Method::get(),
            $protocol = new ProtocolVersion(1, 1),
            Headers::of(),
            new StringStream(''),
            new Environment(new Map('string', 'scalar')),
            new Cookies(new Map('string', 'scalar')),
            new Query(
                Map::of('string', QueryParameterInterface::class)
                    ('range', new Parameter('range', [0, 42]))
            )
        );

        $range = $extract($request);

        $this->assertInstanceOf(Range::class, $range);
        $this->assertSame(0, $range->firstPosition());
        $this->assertSame(42, $range->lastPosition());
    }

    public function testThrowWhenRangeIsNotExactlyAsExpected()
    {
        $extract = new QueryExtractor;
        $request = new ServerRequest(
            Url::fromString('/'),
            Method::get(),
            $protocol = new ProtocolVersion(1, 1),
            Headers::of(),
            new StringStream(''),
            new Environment(new Map('string', 'scalar')),
            new Cookies(new Map('string', 'scalar')),
            new Query(
                Map::of('string', QueryParameterInterface::class)
                    ('range', new Parameter('range', ['resources', 0, 42]))
            )
        );

        $this->expectException(RangeNotFound::class);

        $extract($request);
    }
}
