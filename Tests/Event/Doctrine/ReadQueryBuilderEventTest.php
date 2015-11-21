<?php

namespace Innmind\Rest\Server\Tests\Event\Doctrine;

use Innmind\Rest\Server\Event\Doctrine\ReadQueryBuilderEvent;
use Innmind\Rest\Server\Definition\ResourceDefinition;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityManager;

class ReadQueryBuilderEventTest extends \PHPUnit_Framework_TestCase
{
    protected $e;
    protected $qb;

    public function setUp()
    {
        $this->e = new ReadQueryBuilderEvent(
            new ResourceDefinition('foo'),
            null,
            $this->qb = new QueryBuilder(
                $this
                    ->getMockBuilder(EntityManager::class)
                    ->disableOriginalConstructor()
                    ->getMock()
            )
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
        $qb = new QueryBuilder(
            $this
                ->getMockBuilder(EntityManager::class)
                ->disableOriginalConstructor()
                ->getMock()
        );

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
