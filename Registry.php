<?php

namespace Innmind\Rest\Server;

use Innmind\Rest\Server\Definition\Collection as CollectionDefinition;
use Innmind\Rest\Server\Definition\ResourceDefinition;
use Innmind\Rest\Server\Definition\Property;
use Symfony\Component\Config\Definition\Processor;

class Registry
{
    protected $config;
    protected $processor;
    protected $collections = [];

    public function __construct()
    {
        $this->config = new Configuration;
        $this->processor = new Processor;
    }

    /**
     * Load the given collection array
     *
     * @param string $collection Collection name
     * @param array $config
     *
     * @return Registry self
     */
    public function loadCollection($collection, array $config)
    {
        $config = $this->processor->processConfiguration(
            $this->config,
            [[
                'collections' => [
                    (string) $collection => $config
                ],
            ]]
        );
        $config = $config['collections'][(string) $collection];
        $collection = new CollectionDefinition((string) $collection);
        $collection->setStorage($config['storage']);

        foreach ($config['resources'] as $name => $resource) {
            $r = new ResourceDefinition($name);
            $r
                ->setId($resource['id'])
                ->setStorage($resource['storage']);

            foreach ($resource['properties'] as $propName => $prop) {
                $p = new Property($propName);
                $p->setType($prop['type']);

                foreach ($prop['access'] as $access) {
                    $p->addAccess($access);
                }

                foreach ($prop['variants'] as $variant) {
                    $p->addVariant($variant);
                }

                foreach ($prop['options'] as $optionName => $option) {
                    $p->addOption($optionName, $option);
                }

                $r->addProperty($p);
            }

            foreach ($resource['meta'] as $metaName => $meta) {
                $r->addMeta($metaName, $meta);
            }

            foreach ($resource['options'] as $optionName => $option) {
                $r->addOption($optionName, $option);
            }

            $collection->addResource($r);
        }

        $this->addCollection($collection);

        return $this;
    }

    /**
     * Load a set of collections
     *
     * @param array $collections
     *
     * @return Registry self
     */
    public function load(array $collections)
    {
        $config = $this->processor->processConfiguration(
            $this->config,
            [$collections]
        );

        foreach ($config['collections'] as $name => $collection) {
            $this->loadCollection($name, $collection);
        }

        return $this;
    }

    /**
     * Add a collection of resources
     *
     * @param CollectionDefinition $collection
     *
     * @return Registry self
     */
    public function addCollection(CollectionDefinition $collection)
    {
        $this->collections[(string) $collection] = $collection;

        return $this;
    }

    /**
     * Return the collection
     *
     * @param string $name
     *
     * @throws LogicException If the collection is not found
     *
     * @return CollectionDefinition
     */
    public function getCollection($name)
    {
        if (!$this->hasCollection($name)) {
            throw new \InvalidArgumentException(sprintf(
                'Unknown collection "%s"',
                $name
            ));
        }

        return $this->collections[(string) $name];
    }

    /**
     * Check if the collection exists
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasCollection($name)
    {
        return isset($this->collections[(string) $name]);
    }

    /**
     * Return all collections
     *
     * @return array
     */
    public function getCollections()
    {
        return $this->collections;
    }
}
