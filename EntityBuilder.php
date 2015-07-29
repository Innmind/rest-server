<?php

namespace Innmind\Rest\Server;

use Innmind\Rest\Server\Event\EntityBuildEvent;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class EntityBuilder
{
    protected $accessor;
    protected $dispatcher;

    public function __construct(
        PropertyAccessorInterface $accessor,
        EventDispatcherInterface $dispatcher
    ) {
        $this->accessor = $accessor;
        $this->dispatcher = $dispatcher;
    }

    /**
     * Build an entity out of the given resource
     *
     * @param Resource $resource
     * @param object $entity
     *
     * @throws LogicException If no class specified or if the entity is not an instance of it
     *
     * @return object
     */
    public function build(Resource $resource, $entity = null)
    {
        if (!$resource->getDefinition()->hasOption('class')) {
            throw new \LogicException(sprintf(
                'A "class" must be specified to build an entity for %s::%s',
                $resource->getDefinition()->getCollection(),
                $resource->getDefinition()
            ));
        }

        $class = $resource->getDefinition()->getOption('class');

        if ($entity === null) {
            $entity = new $class;
        } else if (!$entity instanceof $class) {
            throw new \LogicException(sprintf(
                'The given entity must be an instance of %s',
                $class
            ));
        }

        $this->dispatcher->dispatch(
            Events::ENTITY_BUILD,
            $event = new EntityBuildEvent($resource, $entity)
        );

        if ($event->isPropagationStopped()) {
            return $entity;
        }

        foreach ($resource->getProperties() as $key => $value) {
            $this->accessor->setValue($entity, $key, $value);
        }

        return $entity;
    }
}
