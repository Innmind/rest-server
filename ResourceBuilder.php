<?php

namespace Innmind\Rest\Server;

use Innmind\Rest\Server\Definition\Resource as ResourceDefinition;
use Innmind\Rest\Server\Definition\Property;
use Innmind\Rest\Server\Definition\Types;
use Innmind\Rest\Server\Definition\Type\ArrayType;
use Innmind\Rest\Server\Exception\PropertyValidationException;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Validator\ValidatorInterface;

class ResourceBuilder
{
    protected $accessor;
    protected $validator;

    public function __construct(
        PropertyAccessorInterface $accessor,
        ValidatorInterface $validator
    ) {
        $this->accessor = $accessor;
        $this->validator = $validator;
    }

    /**
     * Build a resource object from a raw data
     * object following the given description
     *
     * @param object $data
     * @param ResourceDefinition $definition
     *
     * @throws InvalidArgumentException If the data is not an object
     * @throws NoSuchPropertyException If a property is not found in the data
     *
     * @return Resource
     */
    public function build($data, ResourceDefinition $definition)
    {
        if (!is_object($data)) {
            throw new \InvalidArgumentException(sprintf(
                'You must give a data object in order to build the resource %s',
                $definition
            ));
        }

        $resource = new Resource;
        $resource->setDefinition($definition);

        foreach ($definition->getProperties() as $property) {
            $value = $this->accessor->getValue($data, (string) $property);
            try {
                $this->validateProperty($property, $value);
            } catch(PropertyValidationException $e) {
                $e
                    ->setDefinition($definition)
                    ->setDataObject($data);
                throw new PropertyValidationException(
                    $e->buildMessage(),
                    0,
                    $e
                );
            }
            $resource->set($property, $value);
        }

        return $resource;
    }

    /**
     * Validate a value against its property definition
     *
     * @param Property $property
     * @param mixed $value
     *
     * @return void
     */
    protected function validateProperty(Property $property, $value)
    {
        $type = Types::get($property->getType());

        try {
            if ($type instanceof ArrayType) {
                $type = Types::get($property->getOption('inner_type'));
                foreach ($value as $key => $subValue) {
                    $path = sprintf('%s[%s]', $property, $key);
                    $this->validateValue(
                        $subValue,
                        $type->getConstraints($property)
                    );
                }
            } else {
                $path = (string) $property;
                $this->validateValue(
                    $value,
                    $type->getConstraints($property)
                );
            }
        } catch (PropertyValidationException $e) {
            $e
                ->setPath($path)
                ->setType($type);
            throw $e;
        }
    }

    /**
     * Validate a value against contraints
     *
     * @param mixed $value
     * @param array $constraints
     *
     * @throws PropertyValidationException
     *
     * @return void
     */
    protected function validateValue($value, array $constraints)
    {
        $errors = $this->validator->validateValue($value, $constraints);

        if ($errors->count() > 0) {
            $excpt = new PropertyValidationException;
            $excpt->setConstraintViolation($errors);

            throw $excpt;
        }
    }
}
