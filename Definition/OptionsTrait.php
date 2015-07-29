<?php

namespace Innmind\Rest\Server\Definition;

trait OptionsTrait
{
    protected $options = [];

    /**
     * Add a configuration option
     *
     * @param string $name
     * @param mixed $value
     *
     * @return Property self
     */
    public function addOption($name, $value)
    {
        $this->options[(string) $name] = $value;

        return $this;
    }

    /**
     * Check if the option exists
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasOption($name)
    {
        return isset($this->options[(string) $name]);
    }

    /**
     * Return the option value
     *
     * @param string $name
     *
     * @throws InvalidArgumentException If the option doesn't exist
     *
     * @return mixed
     */
    public function getOption($name)
    {
        if (!$this->hasOption($name)) {
            throw new \InvalidArgumentException(sprintf(
                'Unknown option "%s"',
                $name
            ));
        }

        return $this->options[(string) $name];
    }

    /**
     * Return all options
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }
}
