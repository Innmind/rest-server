<?php

namespace Innmind\Rest\Server\Storage;

use Innmind\Rest\Server\StorageInterface;
use Innmind\Rest\Server\ResourceBuilder;
use Innmind\Rest\Server\Events;
use Innmind\Rest\Server\Collection;
use Innmind\Rest\Server\Event\Storage;
use Innmind\Rest\Server\Event\Doctrine\ReadQueryBuilderEvent;
use Innmind\Rest\Server\Definition\Resource;
use Innmind\Rest\Server\EntityBuilder;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Common\Collections\Collection as DoctrineCollection;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class DoctrineStorage extends AbstractStorage implements StorageInterface
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
    public function read(Resource $definition, $id = null)
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
        $entities = $entities instanceof DoctrineCollection ?
            $entities->toArray() : (array) $entities;
        $resources = new Collection;

        foreach ($entities as $entity) {
            $resources[] = $this->resourceBuilder->build($entity, $definition);
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
    public function supports(Resource $definition)
    {
        if (!$definition->hasOption('class')) {
            return false;
        }

        $class = $definition->getOption('class');

        if ($this->em->getMetadataFactory()->isTransient($class)) {
            return false;
        }

        return true;
    }
}
