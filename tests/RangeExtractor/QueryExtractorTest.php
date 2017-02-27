<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\RangeExtractor;

use Innmind\Rest\Server\{
    RangeExtractor\QueryExtractor,
    RangeExtractor\ExtractorInterface,
    Request\Range
};
use Innmind\Http\{
    Message\ServerRequest,
    Message\Method,
    ProtocolVersion,
    Message\ResponseInterface,
    Message\Environment,
    Message\Cookies,
    Message\Query,
    Message\Query\ParameterInterface as QueryParameterInterface,
    Message\Query\Parameter,
    Message\Form,
    Message\Form\ParameterInterface as FormParameterInterface,
    Message\Files,
    File\FileInterface,
    Headers,
    Header\HeaderInterface
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

        $this->assertInstanceOf(ExtractorInterface::class, $extractor);
    }

    public function testExtract()
    {
        $extractor = new QueryExtractor;
        $request = new ServerRequest(
            Url::fromString('/'),
            new Method('GET'),
            $protocol = new ProtocolVersion(1, 1),
            new Headers(new Map('string', HeaderInterface::class)),
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
            new Files(new Map('string', FileInterface::class))
        );

        $range = $extractor->extract($request);

        $this->assertInstanceOf(Range::class, $range);
        $this->assertSame(0, $range->firstPosition());
        $this->assertSame(42, $range->lastPosition());
    }

    /**
     * @expectedException Innmind\Rest\Server\Exception\RangeNotFoundException
     */
    public function testThrowWhenRangeIsNotExactlyAsExpected()
    {
        $extractor = new QueryExtractor;
        $request = new ServerRequest(
            Url::fromString('/'),
            new Method('GET'),
            $protocol = new ProtocolVersion(1, 1),
            new Headers(new Map('string', HeaderInterface::class)),
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
            new Files(new Map('string', FileInterface::class))
        );

        $extractor->extract($request);
    }
}
