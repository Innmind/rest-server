<?php

namespace Innmind\Rest\Server\Event\Neo4j;

use Innmind\Rest\Server\Event\Storage\PreReadEvent;
use Innmind\Rest\Server\Definition\ResourceDefinition;
use Innmind\Neo4j\ONM\QueryBuilder;

class ReadQueryBuilderEvent extends PreReadEvent
{
    protected $qb;
    protected $qbReplaced = false;

    public function __construct(ResourceDefinition $resource, $id, QueryBuilder $qb)
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
        $this->qbReplaced = true;

        return $this;
    }

    /**
     * Check if the query builder has been replaced by someone
     *
     * @return bool
     */
    public function hasQueryBuilderBeenReplaced()
    {
        return $this->qbReplaced;
    }
}
