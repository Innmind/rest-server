<?php

namespace Innmind\Rest\Server\Definition\Type;

use Innmind\Rest\Server\Definition\TypeInterface;
use Innmind\Rest\Server\Definition\Property;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class ArrayType implements TypeInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConstraints(Property $property)
    {
        $closure = function($data, ExecutionContextInterface $context) use ($property) {
            if (
                !is_array($data) &&
                !$data instanceof \ArrayAccess &&
                !$data instanceof \Traversable
            ) {
                $context
                    ->buildViolation(
                        'It must be an array or an object implementing ' .
                        '\ArrayAccess or \Traversable'
                    )
                    ->atPath((string) $property)
                    ->addViolation();

                return;
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
