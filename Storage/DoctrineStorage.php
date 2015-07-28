<?php

namespace Innmind\Rest\Server\Storage;

use Innmind\Rest\Server\StorageInterface;
use Innmind\Rest\Server\Resource;
use Innmind\Rest\Server\ResourceBuilder;
use Innmind\Rest\Server\Events;
use Innmind\Rest\Server\Event\Storage;
use Innmind\Rest\Server\Event\Doctrine\ReadQueryBuilderEvent;
use Innmind\Rest\Server\Definition\Resource as ResourceDefinition;
use Innmind\Rest\Server\Exception\ResourceNotSupportedException;
use Innmind\Rest\Server\EntityBuilder;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class DoctrineStorage implements StorageInterface
{
    protected $em;
    protected $dispatcher;
    protected $entityBuilder;
    protected $resourceBuilder;

    public function __construct(
        EntityManagerInterface $em,
        EventDispatcherInterface $dispatcher,
        EntityBuilder $entityBuilder,
        ResourceBuilder $resourceBuilder
    ) {
        $this->em = $em;
        $this->dispatcher = $dispatcher;
        $this->entityBuilder = $entityBuilder;
        $this->resourceBuilder = $resourceBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function read(ResourceDefinition $definition, $id = null)
    {
        $this->dispatcher->dispatch(
            Events::STORAGE_PRE_READ,
            $event = new Storage\PreReadEvent($definition, $id)
        );

        if ($event->hasResources()) {
            return $event->getResources();
        }

        $this->checkSupport($definition);

        $qb = $this->em
            ->createQueryBuilder()
            ->select('r')
            ->from($definition->getOption('class'), 'r');

        if ($id !== null) {
            $qb
                ->where(sprintf(
                    'r.%s = :id',
                    $definition->getId()
                ))
                ->setParameter('id', $id);
        }

        $this->dispatcher->dispatch(
            Events::DOCTRINE_READ_QUERY_BUILDER,
            $event = new ReadQueryBuilderEvent($definition, $id, $qb)
        );

        if ($event->hasResources()) {
            return $event->getResources();
        }

        $entities = $event->getQueryBuilder()->getQuery()->getResult();
        $entities = $entities instanceof Collection ?
            $entities->toArray() : (array) $entities;
        $resources = new \SplObjectStorage;

        foreach ($entities as $entity) {
            $resources->attach(
                $this->resourceBuilder->build($entity, $definition)
            );
        }

        $this->dispatcher->dispatch(
            Events::STORAGE_POST_READ,
            $event = new Storage\PostReadEvent($definition, $id, $resources)
        );

        $resources = $event->getResources();
        $resources->rewind();

        return $resources;
    }

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
    public function supports(ResourceDefinition $definition)
    {
        if (!$definition->hasOption('class')) {
            return false;
        }

        $class = $definition->getOption('class');

        if (!$this->em->getMetadataFactory()->hasMetadataFor($class)) {
            return false;
        }

        return true;
    }

    /**
     * Verify if the doctrine storage can allow this resource definition
     *
     * @param ResourceDefinition $definition
     *
     * @throws ResourceNotSupportedException In case the storage can't
     */
    protected function checkSupport(ResourceDefinition $definition)
    {
        if (!$this->supports($definition)) {
            throw new ResourceNotSupportedException(sprintf(
                'Doctrine can\'t support the resource %s::%s',
                $definition->getCollection(),
                $definition
            ));
        }
    }
}
