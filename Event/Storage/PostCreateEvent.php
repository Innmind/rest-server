<?php

namespace Innmind\Rest\Server\Event\Storage;

use Innmind\Rest\Server\Resource;

class PostCreateEvent extends PreCreateEvent
{
    protected $entity;

    public function __construct(Resource $resource, $entity)
    {
        parent::__construct($resource);

        $this->entity = $entity;
    }

    /**
     * Return the created entity
     *
     * @return object $entity
     */
    public function getEntity()
    {
        return $this->entity;
    }
}
