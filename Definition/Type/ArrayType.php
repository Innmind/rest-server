<?php

namespace Innmind\Rest\Server\Definition\Type;

use Innmind\Rest\Server\Definition\TypeInterface;
use Innmind\Rest\Server\Definition\Property;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\ExecutionContextInterface;

class ArrayType implements TypeInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConstraints(Property $property)
    {
        $closure = function($data, ExecutionContextInterface $context) use ($property) {
            if (
                !is_array($data) ||
                !$data instanceof \ArrayAccess ||
                !$data instanceof \Traversable
            ) {
                $context->addViolationAt(
                    (string) $property,
                    'It must be an array or an object implementing \ArrayAccess and \Traversable'
                );
            }
        };

        return [new Callback($closure)];
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return 'array';
    }
}
