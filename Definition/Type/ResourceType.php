<?php

namespace Innmind\Rest\Server\Definition\Type;

use Innmind\Rest\Server\Definition\TypeInterface;
use Innmind\Rest\Server\Definition\Property;
use Innmind\Rest\Server\HttpResourceInterface;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class ResourceType implements TypeInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConstraints(Property $property)
    {
        $closure = function($data, ExecutionContextInterface $context) use ($property) {
            if (!$data instanceof HttpResourceInterface) {
                $context
                    ->buildViolation(sprintf(
                        'A resource must be an instance of %s',
                        HttpResourceInterface::class
                    ))
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
        return 'resource';
    }
}
