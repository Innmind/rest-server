<?php

namespace Innmind\Rest\Server;

class Storages
{
    protected $storages = [];

    /**
     * Add a new storage
     *
     * @param string $name
     * @param StorageInterface $storage
     *
     * @return Storages self
     */
    public function add($name, StorageInterface $storage)
    {
        $this->storages[(string) $name] = $storage;

        return $this;
    }

    /**
     * Check if the storage is defined
     *
     * @param string $name
     *
     * @return bool
     */
    public function has($name)
    {
        return isset($this->storages[(string) $name]);
    }

    /**
     * Return the wished storage
     *
     * @param string $name
     *
     * @throws InvalidArgumentException If the storage doesn't exist
     *
     * @return StorageInterface
     */
    public function get($name)
    {
        if (!$this->has($name)) {
            throw new \InvalidArgumentException(sprintf(
                'The storage "%s" doesn\'t exist',
                $name
            ));
        }

        return $this->storages[(string) $name];
    }
}
