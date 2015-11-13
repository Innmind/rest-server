<?php

namespace Innmind\Rest\Server\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

class RegisterStoragePass implements CompilerPassInterface
{
    protected $serviceId;
    protected $tag;

    public function __construct($serviceId = 'storages', $tag = 'storage')
    {
        $this->serviceId = $serviceId;
        $this->tag = $tag;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $ids = $container->findTaggedServiceIds($this->tag);
        $def = $container->getDefinition($this->serviceId);

        foreach ($ids as $id => $tags) {
            foreach ($tags as $tag) {
                if (!isset($tag['alias'])) {
                    throw new \LogicException(sprintf(
                        'You must specify an alias for the storage %s',
                        $id
                    ));
                }

                $def->addMethodCall('add', [$tag['alias'], new Reference($id)]);
            }
        }
    }
}
