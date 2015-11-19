<?php

namespace Innmind\Rest\Server;

use Innmind\Rest\Server\Definition\ResourceDefinition;

class HttpResource implements HttpResourceInterface
{
    protected $data = [];
    protected $definition;

    /**
     * {@inheritdoc}
     */
    public function set($property, $value)
    {
        $this->data[(string) $property] = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function get($property)
    {
        if (!$this->has($property)) {
            throw new \InvalidArgumentException(sprintf(
                'Unknown property "%s"',
                $property
            ));
        }

        return $this->data[(string) $property];
    }

    /**
     * {@inheritdoc}
     */
    public function has($property)
    {
        return isset($this->data[(string) $property]);
    }

    /**
     * {@inheritdoc}
     */
    public function getProperties()
    {
        return $this->data;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefinition(ResourceDefinition $definition)
    {
        $this->definition = $definition;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        return $this->definition;
    }

    /**
     * {@inheritdoc}
     */
    public function hasDefinition()
    {
        return $this->definition !== null;
    }
}
