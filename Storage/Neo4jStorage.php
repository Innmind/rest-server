<?php

namespace Innmind\Rest\Server\Storage;

use Innmind\Rest\Server\StorageInterface;
use Innmind\Rest\Server\EntityBuilder;
use Innmind\Rest\Server\ResourceBuilder;
use Innmind\Rest\Server\Definition\ResourceDefinition;
use Innmind\Rest\Server\Events;
use Innmind\Rest\Server\Collection;
use Innmind\Rest\Server\Event\Storage;
use Innmind\Rest\Server\Event\Neo4j\ReadQueryBuilderEvent;
use Innmind\Neo4j\ONM\EntityManagerInterface;
use Innmind\Neo4j\ONM\Mapping\NodeMetadata;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Neo4jStorage extends AbstractStorage implements StorageInterface
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
        $uow = $this->em->getUnitOfWork();
        $class = $definition->getOption('class');
        $classMetadata = $uow
            ->getMetadataRegistry()
            ->getMetadata($class);
        $qb = $this->em
            ->getRepository($class)
            ->getQueryBuilder();

        if ($classMetadata instanceof NodeMetadata) {
            if ($id === null) {
                $qb->matchNode('r', $class);
            } else {
                $qb->matchNode('r', $class, [
                    $classMetadata->getId()->getProperty() => $id,
                ]);
            }
        } else {
            if ($id === null) {
                $match = $qb
                    ->expr()
                    ->matchRelationship('r', $class);
            } else {
                $match = $qb
                    ->expr()
                    ->matchRelationship('r', $class, [
                        $classMetadata->getId()->getProperty() => $id,
                    ]);
            }

            $qb->addExpr(
                $qb
                    ->expr()
                    ->matchNode()
                    ->relatedTo($match)
            );
        }

        $this->dispatcher->dispatch(
            Events::NEO4J_READ_QUERY_BUILDER,
            $event = new ReadQueryBuilderEvent($definition, $id, $qb)
        );

        if ($event->hasResources()) {
            return $event->getResources();
        }

        $qb = $event->getQueryBuilder();

        if (!$event->hasQueryBuilderBeenReplaced()) {
            $qb->toReturn('r');
        }

        $query = $qb->getQuery();
        $entities = $uow->execute($query);
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
    public function supports(ResourceDefinition $definition)
    {
        if (!$definition->hasOption('class')) {
            return false;
        }

        $class = $definition->getOption('class');

        if (!$this->em->getUnitOfWork()->getIdentityMap()->has($class)) {
            return false;
        }

        return true;
    }
}
