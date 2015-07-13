<?php

namespace Innmind\Rest\Server\Definition;

class Property
{
    protected $name;
    protected $type;
    protected $access = [];
    protected $variants = [];
    protected $options = [];

    public function __construct($name)
    {
        $this->name = (string) $name;
    }

    /**
     * Return the property name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the type allowed for this property
     *
     * @param string $type
     *
     * @return Property self
     */
    public function setType($type)
    {
        $this->type = (string) $type;

        return $this;
    }

    /**
     * Return the property type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Add an access flag
     *
     * @param string $flag
     *
     * @return Property self
     */
    public function addAccess($flag)
    {
        if (!in_array((string) $flag, $this->access, true)) {
            $this->access[] = (string) $flag;
        }

        return $this;
    }

    /**
     * Return all the access flags
     *
     * @return array
     */
    public function getAccess()
    {
        return $this->access;
    }

    /**
     * Add a variant name for this property
     *
     * @param string $name
     *
     * @throws LogicException If the variant is already used
     *
     * @return Property self
     */
    public function addVariant($name)
    {
        $name = (string) $name;

        if ($this->name === $name || in_array($name, $this->variants, true)) {
            throw new \LogicException(sprintf(
                'Property name "%s" is already used',
                $name
            ));
        }

        $this->variants[] = $name;

        return $this;
    }

    /**
     * Check if the property has a variant
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasVariant($name)
    {
        return in_array((string) $name, $this->variants, true);
    }

    /**
     * Return al the variants
     *
     * @return array
     */
    public function getVariants()
    {
        return $this->variants;
    }

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
                'Unknown option "%s" for property "%s"',
                $name,
                $this->name
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

    public function __toString()
    {
        return $this->name;
    }
}
