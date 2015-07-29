<?php

namespace Innmind\Rest\Server\Event\Neo4j;

use Innmind\Rest\Server\Event\Storage\PreReadEvent;
use Innmind\Rest\Server\Definition\Resource;
use Innmind\Neo4j\ONM\QueryBuilder;

class ReadQueryBuilderEvent extends PreReadEvent
{
    protected $qb;

    public function __construct(Resource $resource, $id, QueryBuilder $qb)
    {
        parent::__construct($resource, $id);

        $this->qb = $qb;
    }

    /**
     * Return the query builder
     *
     * @return QueryBuilder
     */
    public function getQueryBuilder()
    {
        return $this->qb;
    }

    /**
     * Replace completely the query builder by the given one
     *
     * @param QueryBuilder $qb
     *
     * @return ReadQueryBuilderEvent self
     */
    public function replaceQueryBuilder(QueryBuilder $qb)
    {
        $this->qb = $qb;

        return $this;
    }
}
