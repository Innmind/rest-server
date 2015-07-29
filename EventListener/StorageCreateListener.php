<?php

namespace Innmind\Rest\Server\EventListener;

use Innmind\Rest\Server\Events;
use Innmind\Rest\Server\Event\Storage\PostCreateEvent;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Listener used to extract the id from the entity
 */
class StorageCreateListener implements EventSubscriberInterface
{
    protected $accessor;

    public function __construct(PropertyAccessorInterface $accessor)
    {
        $this->accessor = $accessor;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            Events::STORAGE_POST_CREATE => 'extractId',
        ];
    }

    /**
     * Extract the id off of the entity and set it in the event
     * so it can be returned to the developer
     *
     * @param PostCreateEvent $event
     *
     * @return void
     */
    public function extractId(PostCreateEvent $event)
    {
        $entity = $event->getEntity();
        $def = $event->getResource()->getDefinition();

        if ($this->accessor->isReadable($entity, $def->getId())) {
            $id = $this->accessor->getValue($entity, $def->getId());
        } else {
            $refl = new \ReflectionObject($entity);
            $refl = $refl->getProperty($def->getId());
            $accessible = $refl->isPublic();

            if (!$accessible) {
                $refl->setAccessible(true);
            }

            $id = $refl->getValue($entity);

            if (!$accessible) {
                $refl->setAccessible(false);
            }
        }

        if (isset($id)) {
            $event->setResourceId($id);
            $event->stopPropagation();
        }
    }
}
