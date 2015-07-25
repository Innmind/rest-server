<?php

namespace Innmind\Rest\Server\Definition;

class Resource
{
    use OptionsTrait;

    protected $name;
    protected $id;
    protected $properties = [];
    protected $meta = [];
    protected $collection;
    protected $storage;

    public function __construct($name)
    {
        $this->name = (string) $name;
    }

    /**
     * Return the resource name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the property name used as id
     *
     * @param string $id
     *
     * @return Resource self
     */
    public function setId($id)
    {
        $this->id = (string) $id;

        return $this;
    }

    /**
     * Return the id property name
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Add a property definition
     *
     * @param Property $property
     *
     * @throws LogicException If the property name or variant conflicts with another property
     *
     * @return Resource self
     */
    public function addProperty(Property $property)
    {
        foreach ($this->properties as $prop) {
            $variants = $property->getVariants();
            array_unshift($variants, $property->getName());

            foreach ($variants as $variant) {
                if (
                    $prop->getName() === $variant ||
                    $prop->hasVariant($variant)
                ) {
                    throw new \LogicException(sprintf(
                        'The property "%s" conflicts with "%s" on "%s"',
                        $property->getName(),
                        $prop->getName(),
                        $variant
                    ));
                }
            }
        }

        $this->properties[$property->getName()] = $property;

        return $this;
    }

    /**
     * Return all properties
     *
     * @return array
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * Return a property
     *
     * @param string $name
     *
     * @throws InvalidArgumentException If property not found
     *
     * @return Property
     */
    public function getProperty($name)
    {
        if (!isset($this->properties[(string) $name])) {
            throw new \InvalidArgumentException(sprintf(
                'Unknown property "%s" for resource "%s"',
                $name,
                $this->name
            ));
        }

        return $this->properties[(string) $name];
    }

    /**
     * Check if the property exists
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasProperty($name)
    {
        return isset($this->properties[(string) $name]);
    }

    /**
     * Add a meta data
     *
     * @param string $name
     * @param mixed $value
     *
     * @return Resource self
     */
    public function addMeta($name, $value)
    {
        $this->meta[(string) $name] = $value;

        return $this;
    }

    /**
     * Return all the meta data
     *
     * @return array
     */
    public function getMetas()
    {
        return $this->meta;
    }

    /**
     * Return a meta data
     *
     * @param string $name
     *
     * @throws InvalidArgumentException If the meta is not found
     *
     * @return mixed
     */
    public function getMeta($name)
    {
        if (!isset($this->meta[(string) $name])) {
            throw new \InvalidArgumentException(sprintf(
                'Unknown meta "%s" for resource "%s"',
                $name,
                $this->name
            ));
        }

        return $this->meta[(string) $name];
    }

    /**
     * Check if the meta exists
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasMeta($name)
    {
        return isset($this->meta[(string) $name]);
    }

    /**
     * Set the collection it's attached to
     *
     * @param Collection $collection
     *
     * @return Resource self
     */
    public function setCollection(Collection $collection)
    {
        $this->collection = $collection;

        return $this;
    }

    /**
     * Return the collection it's attached to
     *
     * @return Collection
     */
    public function getCollection()
    {
        return $this->collection;
    }

    /**
     * Set the storage to use with this resource
     *
     * @param string $storage
     *
     * @return Resource self
     */
    public function setStorage($storage)
    {
        $this->storage = (string) $storage;

        return $this;
    }

    /**
     * Check a storage is defined
     *
     * @return bool
     */
    public function hasStorage()
    {
        return $this->storage !== null;
    }

    /**
     * Return the storage ot use with this resource
     *
     * @return string
     */
    public function getStorage()
    {
        return $this->storage;
    }

    public function __toString()
    {
        return $this->name;
    }
}
