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
};
use Innmind\Immutable\{
    MapInterface,
    Map,
    Set,
    Sequence,
};
use Symfony\Component\{
    Config\Definition\Processor,
    Yaml\Yaml,
};

final class YamlLoader implements Loader
{
    private $types;

    public function __construct(Types $types = null)
    {
        $this->types = $types ?? new Types;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(string ...$files): MapInterface
    {
        $config = (new Processor)->processConfiguration(
            new Configuration,
            Set::of('string', ...$files)->reduce(
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
        $properties = new Set(Property::class);

        foreach ($config['properties'] as $key => $value) {
            $properties = $properties->add(
                $this->buildProperty($key, $value)
            );
        }

        $arguments = [
            $name,
            new Gateway($config['gateway']),
            new Identity($config['identity']),
            $properties,
            Map::of(
                'scalar',
                'variable',
                array_keys($config['options'] ?? []),
                array_values($config['options'] ?? [])
            ),
            Map::of(
                'scalar',
                'variable',
                array_keys($config['metas'] ?? []),
                array_values($config['metas'] ?? [])
            ),
            Map::of(
                'string',
                'string',
                array_keys($config['linkable_to'] ?? []),
                array_values($config['linkable_to'] ?? [])
            ),
        ];

        if ($config['rangeable']) {
            return HttpResource::rangeable(...$arguments);
        }

        return new HttpResource(...$arguments);
    }

    private function buildProperty(string $name, array $config): Property
    {
        $type = ($config['optional'] ?? false) ? 'optional' : 'required';

        return Property::$type(
            $name,
            $this->types->build(
                $config['type'],
                Map::of(
                    'scalar',
                    'variable',
                    array_keys($config['options'] ?? []),
                    array_values($config['options'] ?? [])
                )
            ),
            new Access(...($config['access'] ?? [Access::READ])),
            ...($config['variants'] ?? [])
        );
    }
}
