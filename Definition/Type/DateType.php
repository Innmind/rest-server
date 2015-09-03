<?php

namespace Innmind\Rest\Server\Definition\Type;

use Innmind\Rest\Server\Definition\TypeInterface;
use Innmind\Rest\Server\Definition\Property;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class DateType implements TypeInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConstraints(Property $property)
    {
        $closure = function($data, ExecutionContextInterface $context) use ($property) {
            if ($data instanceof \DateTime) {
                return;
            }

            if (!is_string($data)) {
                $context
                    ->buildViolation('This field must be a date')
                    ->atPath((string) $property)
                    ->addViolation();

                return;
            }

            try {
                if ($property->hasOption('format')) {
                    \DateTime::createFromFormat(
                        $property->getOption('format'),
                        $data
                    );
                } else {
                    new \DateTime($data);
                }
            } catch (\Exception $e) {
                $context
                    ->buildViolation('This field must be a date')
                    ->atPath((string) $property)
                    ->addViolation();
            }
        };

        return [new Callback($closure)];
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return 'date';
    }
}
