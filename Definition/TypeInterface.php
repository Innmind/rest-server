<?php

namespace Innmind\Rest\Server\Definition;

interface TypeInterface
{
    /**
     * Return an array of contraints in order to validate the given property
     *
     * @param Property $property
     *
     * @return array
     */
    public function getConstraints(Property $property);

    /**
     * String representation of the type (meaning its shortcode)
     *
     * @return string
     */
    public function __toString();
}
