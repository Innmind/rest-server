<?php

namespace Innmind\Rest\Server;

use Innmind\Rest\Server\DependencyInjection\Compiler\RegisterDefinitionCompilerPass;
use Innmind\Rest\Server\DependencyInjection\Compiler\RegisterStoragePass;
use Innmind\Rest\Server\DependencyInjection\Compiler\RegisterFormatPass;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\EventDispatcher\DependencyInjection\RegisterListenersPass;
use Symfony\Bundle\FrameworkBundle\DependencyInjection\Compiler\SerializerPass;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Yaml\Yaml;

class Application extends HttpKernel
{
    protected $container;

    /**
     * Constructor
     *
     * @param string $resources Path to the yaml resources configuration file
     * @param string $services Path to the yaml services configuration file
     */
    public function __construct($resources, $services)
    {
        $this->container = new ContainerBuilder;
        $this->loadServices($services);
        $this->loadResources($resources);
        $this
            ->container
            ->addCompilerPass(new RegisterListenersPass)
            ->addCompilerPass(new RegisterDefinitionCompilerPass)
            ->addCompilerPass(new RegisterStoragePass)
            ->addCompilerPass(new RegisterFormatPass)
            ->addCompilerPass(new SerializerPass)
            ->compile();

        parent::__construct(
            $this->container->get('event_dispatcher'),
            $this->container->get('controller_resolver')
        );
    }

    /**
     * Load the default services and the ones specified in the given file
     *
     * @param string $services Yaml file path
     *
     * @return void
     */
    protected function loadServices($services)
    {
        $this->container->set('container', $this->container);
        $loader = new YamlFileLoader(
            $this->container,
            new FileLocator(__DIR__ . '/config/services')
        );
        $loader->load('main.yml');

        $services = (string) $services;

        if (is_dir($services)) {
            $dir = $services;
            $file = '*';
        } else {
            $dir = dirname($services);
            $file = basename($services);
        }

        $loader = new YamlFileLoader(
            $this->container,
            new FileLocator($dir)
        );
        $loader->load($file);
    }

    /**
     * Inject the resources in the registry
     *
     * @param string $resources
     *
     * @return void
     */
    protected function loadResources($resources)
    {
        $this
            ->container
            ->getDefinition('registry')
            ->addMethodCall(
                'load',
                [Yaml::parse(file_get_contents($resources))]
            );
    }
}
