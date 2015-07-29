<?php

namespace Innmind\Rest\Server\Storage;

use Innmind\Rest\Server\StorageInterface;
use Innmind\Rest\Server\EntityBuilder;
use Innmind\Rest\Server\ResourceBuilder;
use Innmind\Rest\Server\Definition\Resource;
use Innmind\Rest\Server\Events;
use Innmind\Rest\Server\Event\Storage;
use Innmind\Rest\Server\Event\Neo4j\ReadQueryBuilderEvent;
use Innmind\Rest\Server\Exception\ResourceNotSupportedException;
use Innmind\Neo4j\ONM\EntityManagerInterface;
use Innmind\Neo4j\ONM\Mapping\NodeMetadata;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Neo4jStorage implements StorageInterface
{
    use CreateTrait;
    use UpdateTrait;
    use DeleteTrait;

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

        $query = $event
            ->getQueryBuilder()
            ->toReturn('r')
            ->getQuery();
        $entities = $uow->execute($query);
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
    public function supports(Resource $definition)
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

    /**
     * Verify if the neo4j storage can allow this resource definition
     *
     * @param Resource $definition
     *
     * @throws ResourceNotSupportedException In case the storage can't
     */
    protected function checkSupport(Resource $definition)
    {
        if (!$this->supports($definition)) {
            throw new ResourceNotSupportedException(sprintf(
                'Neo4j can\'t support the resource %s::%s',
                $definition->getCollection(),
                $definition
            ));
        }
    }
}
