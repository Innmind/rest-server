<?php

namespace Innmind\Rest\Server\Tests\Event\Neo4j;

use Innmind\Rest\Server\Event\Neo4j\ReadQueryBuilderEvent;
use Innmind\Rest\Server\Definition\Resource;
use Innmind\Neo4j\ONM\QueryBuilder;

class ReadQueryBuilderEventTest extends \PHPUnit_Framework_TestCase
{
    protected $e;
    protected $qb;

    public function setUp()
    {
        $this->e = new ReadQueryBuilderEvent(
            new Resource('foo'),
            null,
            $this->qb = new QueryBuilder
        );
    }

    public function testGetQueryBuilder()
    {
        $this->assertSame(
            $this->qb,
            $this->e->getQueryBuilder()
        );
    }

    public function testReplaceQueryBuilder()
    {
        $qb = new QueryBuilder;

        $this->assertSame(
            $this->e,
            $this->e->replaceQueryBuilder($qb)
        );
        $this->assertSame(
            $qb,
            $this->e->getQueryBuilder()
        );
    }
}
