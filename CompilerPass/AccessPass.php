<?php

namespace Innmind\Rest\Server\CompilerPass;

use Innmind\Rest\Server\CompilerPassInterface;
use Innmind\Rest\Server\Registry;
use Innmind\Rest\Server\Access;
use Innmind\Rest\Server\Exception\UnknownPropertyAccessException;

class AccessPass implements CompilerPassInterface
{
    protected $allowed = [Access::READ, Access::CREATE, Access::UPDATE];

    /**
     * {@inheritdoc}
     */
    public function process(Registry $registry)
    {
        foreach ($registry->getCollections() as $collection) {
            foreach ($collection->getResources() as $resource) {
                foreach ($resource->getProperties() as $property) {
                    $access = $property->getAccess();

                    if (empty($access)) {
                        throw new UnknownPropertyAccessException(sprintf(
                            'You must specify at least on access for %s::%s.%s',
                            $collection,
                            $resource,
                            $property
                        ));
                    }

                    foreach ($access as $v) {
                        if (!in_array($v, $this->allowed, true)) {
                            throw new UnknownPropertyAccessException(sprintf(
                                'The access "%s" is invalid for %s::%s.%s',
                                $v,
                                $collection,
                                $resource,
                                $property
                            ));
                        }
                    }
                }
            }
        }
    }
}
