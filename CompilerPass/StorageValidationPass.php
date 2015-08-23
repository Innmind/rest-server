<?php

namespace Innmind\Rest\Server\CompilerPass;

use Innmind\Rest\Server\CompilerPassInterface;
use Innmind\Rest\Server\Registry;
use Innmind\Rest\Server\Storages;
use Innmind\Rest\Server\Exception\UnknownStorageException;

class StorageValidationPass implements CompilerPassInterface
{
    protected $storages;

    public function __construct(Storages $storages)
    {
        $this->storages = $storages;
    }

    /**
     * {@inheritdoc}
     */
    public function process(Registry $registry)
    {
        foreach ($registry->getCollections() as $collection) {
            foreach ($collection->getResources() as $resource) {
                if (!$this->storages->has($resource->getStorage())) {
                    throw new UnknownStorageException(sprintf(
                        'Unknown storage "%s" for %s::%s',
                        $resource->getStorage(),
                        $collection,
                        $resource
                    ));
                }
            }
        }
    }
}
