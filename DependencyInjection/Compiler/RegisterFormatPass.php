<?php

namespace Innmind\Rest\Server\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

class RegisterFormatPass implements CompilerPassInterface
{
    protected $serviceId;
    protected $tag;

    public function __construct($serviceId = 'formats', $tag = 'format')
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
        $formats = $container->getDefinition($this->serviceId);

        foreach ($ids as $id => $tags) {
            foreach ($tags as $tag) {
                if (!isset($tag['format'])) {
                    throw new \LogicException(sprintf(
                        'You need to specify a format on the service %s',
                        $id
                    ));
                }

                if (!isset($tag['mime'])) {
                    throw new \LogicException(sprintf(
                        'You need to specify the associated mime type for %s on %s',
                        $tag['format'],
                        $id
                    ));
                }

                $formats->addMethodCall('add', [
                    $tag['format'],
                    $tag['mime'],
                    isset($tag['priority']) ? (int) $tag['priority'] : 0
                ]);
            }
        }
    }
}
