<?php

namespace Innmind\Rest\Server\Event\Doctrine;

use Innmind\Rest\Server\Event\Storage\PreReadEvent;
use Innmind\Rest\Server\Definition\ResourceDefinition;
use Doctrine\ORM\QueryBuilder;

class ReadQueryBuilderEvent extends PreReadEvent
{
    protected $qb;

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

        return $this;
    }
}
