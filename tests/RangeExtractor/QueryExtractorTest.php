<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\RangeExtractor;

use Innmind\Rest\Server\{
    RangeExtractor\QueryExtractor,
    RangeExtractor\Extractor,
    Request\Range
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
    Message\Form\Form,
    Message\Form\Parameter as FormParameterInterface,
    Message\Files\Files,
    File,
    Headers\Headers,
    Header
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
        $extractor = new QueryExtractor;
        $request = new ServerRequest(
            Url::fromString('/'),
            new Method('GET'),
            $protocol = new ProtocolVersion(1, 1),
            new Headers(new Map('string', Header::class)),
            new StringStream(''),
            new Environment(new Map('string', 'scalar')),
            new Cookies(new Map('string', 'scalar')),
            new Query(
                (new Map('string', QueryParameterInterface::class))
                    ->put(
                        'range',
                        new Parameter('range', [0, 42])
                    )
            ),
            new Form(new Map('scalar', FormParameterInterface::class)),
            new Files(new Map('string', File::class))
        );

        $range = $extractor->extract($request);

        $this->assertInstanceOf(Range::class, $range);
        $this->assertSame(0, $range->firstPosition());
        $this->assertSame(42, $range->lastPosition());
    }

    /**
     * @expectedException Innmind\Rest\Server\Exception\RangeNotFound
     */
    public function testThrowWhenRangeIsNotExactlyAsExpected()
    {
        $extractor = new QueryExtractor;
        $request = new ServerRequest(
            Url::fromString('/'),
            new Method('GET'),
            $protocol = new ProtocolVersion(1, 1),
            new Headers(new Map('string', Header::class)),
            new StringStream(''),
            new Environment(new Map('string', 'scalar')),
            new Cookies(new Map('string', 'scalar')),
            new Query(
                (new Map('string', QueryParameterInterface::class))
                    ->put(
                        'range',
                        new Parameter('range', ['resources', 0, 42])
                    )
            ),
            new Form(new Map('scalar', FormParameterInterface::class)),
            new Files(new Map('string', File::class))
        );

        $extractor->extract($request);
    }
}
