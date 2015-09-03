<?php

namespace Innmind\Rest\Server\Definition\Type;

use Innmind\Rest\Server\Definition\TypeInterface;
use Innmind\Rest\Server\Definition\Property;
use Symfony\Component\Validator\Constraints\Type;

class FloatType implements TypeInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConstraints(Property $property)
    {
        return [new Type(['type' => 'float'])];
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return 'float';
    }
}
