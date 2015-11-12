<?php

namespace Innmind\Rest\Server\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

class RegisterDefinitionCompilerPass implements CompilerPassInterface
{
    protected $serviceId;
    protected $tag;

    public function __construct(
        $serviceId = 'definition_compiler',
        $tag = 'definition.pass'
    ) {
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
            $def->addMethodCall('addCompilerPass', [new Reference($id)]);
        }
    }
}
