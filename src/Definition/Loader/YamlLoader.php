<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Definition\Loader;

use Innmind\Rest\Server\{
    Definition\Types,
    Definition\Directory,
    Definition\HttpResource,
    Definition\Property,
    Definition\Identity,
    Definition\Gateway,
    Definition\Access,
    Definition\LoaderInterface,
    Configuration,
    Exception\InvalidArgumentException,
    Exception\ResourceDefinitionReferenceNotFoundException,
    Exception\CircularReferenceException
};
use Innmind\Immutable\{
    SetInterface,
    MapInterface,
    Map,
    Collection,
    Set,
    Sequence,
    StringPrimitive as Str
};
use Symfony\Component\{
    Config\Definition\Processor,
    Yaml\Yaml
};

final class YamlLoader implements LoaderInterface
{
    private $types;
    private $config;
    private $directories;
    private $currentPath;
    private $loaded;

    public function __construct(Types $types)
    {
        $this->types = $types;
    }

    /**
     * {@inheritdoc}
     */
    public function load(SetInterface $files): MapInterface
    {
        if ((string) $files->type() !== 'string') {
            throw new InvalidArgumentException;
        }

        $this->config = (new Processor)->processConfiguration(
            new Configuration,
            $files->reduce(
                [],
                function(array $carry, string $file) {
                    $carry[] = Yaml::parse(file_get_contents($file));

                    return $carry;
                }
            )
        );

        $this->currentPath = new Sequence;
        $this->loaded = new Map('string', 'object');
        $this->directories = new Map('string', Directory::class);

        foreach ($this->config as $key => $value) {
            $this->directories = $this->directories->put(
                $key,
                $this->loadDirectory($key, $value)
            );
        }

        $this->currentPath = null;
        $this->loaded = null;

        return $this->directories;
    }

    private function loadDirectory(string $name, array $config): Directory
    {
        $currentPath = $this->currentPath->add($name);

        if ($this->loaded->contains((string) $currentPath->join('.'))) {
            return $this->loaded->get((string) $currentPath->join('.'));
        }

        $this->currentPath = $currentPath;
        $children = new Map('string', Directory::class);
        $definitions = new Map('string', HttpResource::class);

        $config = (new Processor)->processConfiguration(
            new Configuration,
            [[$name => $config]]
        )[$name];

        foreach ($config['children'] ?? [] as $key => $child) {
            $children = $children->put(
                $key,
                $this->loadDirectory($key, $child)
            );
        }

        foreach ($config['resources'] ?? [] as $key => $resource) {
            $definitions = $definitions->put(
                $key,
                $this->loadDefinition($key, $resource)
            );
        }

        $directory = new Directory($name, $children, $definitions);
        $this->loaded = $this->loaded->put(
            (string) $this->currentPath->join('.'),
            $directory
        );
        $this->currentPath = $this->currentPath->dropEnd(1);

        return $directory;
    }

    private function loadDefinition(string $name, array $config): HttpResource
    {
        $currentPath = $this->currentPath->add($name);

        if ($this->loaded->contains((string) $currentPath->join('.'))) {
            return $this->loaded->get((string) $currentPath->join('.'));
        }

        $this->currentPath = $currentPath;
        $definition = $this->buildDefinition($name, $config);
        $this->loaded = $this->loaded->put(
            (string) $this->currentPath->join('.'),
            $definition
        );
        $this->currentPath = $this->currentPath->dropEnd(1);

        return $definition;
    }

    private function buildDefinition(string $name, array $config): HttpResource
    {
        $properties = new Map('string', Property::class);
        $options = new Map('scalar', 'variable');
        $metas = new Map('scalar', 'variable');
        $links = new Map('string', 'string');

        foreach ($config['properties'] as $key => $value) {
            $properties = $properties->put(
                $key,
                $this->buildProperty($key, $value)
            );
        }

        foreach ($config['options'] ?? [] as $key => $value) {
            $options = $options->put($key, $value);
        }

        foreach ($config['metas'] ?? [] as $key => $value) {
            $metas = $metas->put($key, $value);
        }

        foreach ($config['linkable_to'] ?? [] as $key => $value) {
            $links = $links->put($key, $value);
        }

        return new HttpResource(
            $name,
            new Identity($config['identity']),
            $properties,
            $options,
            $metas,
            new Gateway($config['gateway']),
            $config['rangeable'],
            $links
        );
    }

    private function buildProperty(string $name, array $config): Property
    {
        $access = new Set('string');
        $variants = new Set('string');

        foreach ($config['access'] ?? [Access::READ] as $value) {
            $access = $access->add($value);
        }

        foreach ($config['variants'] ?? [] as $variant) {
            $variants = $variants->add($variant);
        }

        $collection = new Collection($config['options']);

        if ($collection->hasKey('resource')) {
            $collection->set(
                'resource',
                $this->locate($collection->get('resource'))
            );
        }

        return new Property(
            $name,
            $this->types->build(
                $config['type'],
                $collection
            ),
            new Access($access),
            $variants,
            $config['optional'] ?? false
        );
    }

    /**
     * Load the definition at the given path
     */
    private function locate(string $path): HttpResource
    {
        if ($this->loaded->contains($path)) {
            return $this->loaded->get($path);
        }

        if ((string) $this->currentPath->join('.') === $path) {
            throw new CircularReferenceException($path);
        }

        $pieces = (new Str($path))->split('.');

        try {
            $config = $pieces->reduce(
                function(array $config, string $path) {
                    return $config[$path] ?? $config['resources'][$path];
                },
                $this->config
            );
        } catch (\Throwable $e) {
            throw new ResourceDefinitionReferenceNotFoundException($path);
        }

        $definition = $this->buildDefinition(
            (string) $pieces->last(),
            $config
        );
        $this->loaded = $this->loaded->put($path, $definition);

        return $definition;
    }
}
