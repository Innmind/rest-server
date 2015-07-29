<?php

namespace Innmind\Rest\Server\Storage;

use Innmind\Rest\Server\Resource;
use Innmind\Rest\Server\Events;
use Innmind\Rest\Server\Event\Storage;

/**
 * Trait containing a method to easily implement StorageInterface::create
 */
trait CreateTrait
{
    /**
     * {@inheritdoc}
     *
     * The method expects a dispatcher, an entity builder, an entity manager
     * and the method checkSupport to exist in your storage
     */
    public function create(Resource $resource)
    {
        $this->dispatcher->dispatch(
            Events::STORAGE_PRE_CREATE,
            $event = new Storage\PreCreateEvent($resource)
        );

        if ($event->hasResourceId()) {
            return $event->getResourceId();
        }

        $this->checkSupport($event->getResource()->getDefinition());

        $entity = $this->entityBuilder->build($event->getResource());

        $this->em->persist($entity);
        $this->em->flush();

        $this->dispatcher->dispatch(
            Events::STORAGE_POST_CREATE,
            $event = new Storage\PostCreateEvent($event->getResource(), $entity)
        );

        return $event->getResourceId();
    }
}
