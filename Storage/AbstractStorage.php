<?php

namespace Innmind\Rest\Server\Storage;

use Innmind\Rest\Server\Resource;
use Innmind\Rest\Server\Events;
use Innmind\Rest\Server\Event\Storage;
use Innmind\Rest\Server\Definition\ResourceDefinition;
use Innmind\Rest\Server\Exception\ResourceNotSupportedException;

abstract class AbstractStorage
{
    protected $em;
    protected $dispatcher;
    protected $entityBuilder;
    protected $resourceBuilder;

    /**
     * {@inheritdoc}
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

    /**
     * {@inheritdoc}
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

    /**
     * {@inheritdoc}
     */
    public function delete(ResourceDefinition $definition, $id)
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
     * {@inheritdoc}
     */
    abstract protected function supports(ResourceDefinition $definition);

    /**
     * Verify if the storage can allow this resource definition
     *
     * @param ResourceDefinition $definition
     *
     * @throws ResourceNotSupportedException In case the storage can't
     */
    protected function checkSupport(ResourceDefinition $definition)
    {
        if (!$this->supports($definition)) {
            throw new ResourceNotSupportedException(sprintf(
                'Storage can\'t support the resource %s::%s',
                $definition->getCollection(),
                $definition
            ));
        }
    }
}
