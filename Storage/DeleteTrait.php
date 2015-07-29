<?php

namespace Innmind\Rest\Server\Storage;

use Innmind\Rest\Server\Definition\Resource;
use Innmind\Rest\Server\Event\Storage;
use Innmind\Rest\Server\Events;

/**
 * Trait containing method to easily comply with StorageInterface::delete
 */
trait DeleteTrait
{
    protected $dispatcher;
    protected $em;

    /**
     * {@inheritdoc}
     *
     * The method expects a dispather, an entity manager
     * and the method checkSupport to exist in your storage
     */
    public function delete(Resource $definition, $id)
    {
        $this->dispatcher->dispatch(
            Events::STORAGE_PRE_DELETE,
            $event = new Storage\PreDeleteEvent($definition, $id)
        );

        if ($event->isPropagationStopped()) {
            return $this;
        }

        $this->checkSupport($definition);

        $entity = $this->em->find($definition->getOption('class'), $id);
        $this->em->remove($entity);
        $this->em->flush();

        $this->dispatcher->dispatch(
            Events::STORAGE_POST_DELETE,
            new Storage\PostDeleteEvent($definition, $id, $entity)
        );

        return $this;
    }

    /**
     * Dumb implementation to not break create method
     */
    protected function checkSupport(Resource $definition)
    {
    }
}
