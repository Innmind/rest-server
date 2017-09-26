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
    Definition\Loader,
    Configuration,
    Exception\InvalidArgumentException,
    Exception\ResourceDefinitionReferenceNotFoundException,
    Exception\CircularReferenceException
};
use Innmind\Immutable\{
    SetInterface,
    MapInterface,
    Map,
    Set,
    Sequence
};
use Symfony\Component\{
    Config\Definition\Processor,
    Yaml\Yaml
};

final class YamlLoader implements Loader
{
    private $types;

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

        $config = (new Processor)->processConfiguration(
            new Configuration,
            $files->reduce(
                [],
                function(array $carry, string $file) {
                    $carry[] = Yaml::parse(file_get_contents($file));

                    return $carry;
                }
            )
        );

        $directories = new Map('string', Directory::class);

        foreach ($config as $key => $value) {
            $directories = $directories->put(
                $key,
                $this->loadDirectory($key, $value)
            );
        }

        return $directories;
    }

    private function loadDirectory(string $name, array $config): Directory
    {
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
                $this->buildDefinition($key, $resource)
            );
        }

        return new Directory($name, $children, $definitions);
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

        $collection = new Map('scalar', 'variable');

        foreach ($config['options'] ?? [] as $key => $value) {
            $collection = $collection->put($key, $value);
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
}
