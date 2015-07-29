<?php

namespace Innmind\Rest\Server\Storage;

use Innmind\Rest\Server\Resource;
use Innmind\Rest\Server\Events;
use Innmind\Rest\Server\Event\Storage;

/**
 * Trait containing method to easily implement StorageInterface::update
 */
trait UpdateTrait
{
    /**
     * {@inheritdoc}
     *
     * The method expects a dispatcher, an entity builder, an entity manager
     * and the method checkSupport to exist in your storage
     */
    public function update(Resource $resource, $id)
    {
        $this->dispatcher->dispatch(
            Events::STORAGE_PRE_UPDATE,
            $event = new Storage\PreUpdateEvent($resource, $id)
        );

        if ($event->isPropagationStopped()) {
            return $this;
        }

        $resource = $event->getResource();
        $this->checkSupport($resource->getDefinition());

        $entity = $this->em->find(
            $resource->getDefinition()->getOption('class'),
            $id
        );
        $this->entityBuilder->build($resource, $entity);

        $this->em->flush();

        $this->dispatcher->dispatch(
            Events::STORAGE_POST_UPDATE,
            new Storage\PostUpdateEvent($resource, $id, $entity)
        );

        return $this;
    }
}
