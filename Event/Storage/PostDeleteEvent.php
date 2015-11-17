<?php

namespace Innmind\Rest\Server\Event\Storage;

use Innmind\Rest\Server\Definition\ResourceDefinition;

class PostDeleteEvent extends PreDeleteEvent
{
    protected $entity;

    public function __construct(ResourceDefinition $definition, $id, $entity)
    {
        parent::__construct($definition, $id);

        $this->entity = $entity;
    }

    /**
     * Return the entity that has been removed
     *
     * @return object
     */
    public function getEntity()
    {
        return $this->entity;
    }
}
