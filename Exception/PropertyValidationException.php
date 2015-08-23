<?php

namespace Innmind\Rest\Server\Exception;

use Innmind\Rest\Server\Definition\Resource;
use Innmind\Rest\Server\Definition\TypeInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class PropertyValidationException extends \Exception
{
    protected $path;
    protected $definition;
    protected $dataObject;
    protected $type;
    protected $constraintViolation;

    /**
     * Set the property path where the validation error occured
     *
     * @param string $path
     *
     * @return PropertyValidationException self
     */
    public function setPath($path)
    {
        $this->path = (string) $path;

        return $this;
    }

    /**
     * Return the path where the validation error occured
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Set the resource definition on which the error occured
     *
     * @param Innmind\Rest\Server\Definition\Resource $resource
     *
     * @return PropertyValidationException self
     */
    public function setDefinition(Resource $resource)
    {
        $this->definition = $resource;

        return $this;
    }

    /**
     * Return the resource definition
     *
     * @return Resource
     */
    public function getDefinition()
    {
        return $this->definition;
    }

    /**
     * Set the data object having the faulty data
     *
     * @param object $data
     *
     * @return PropertyValidationException self
     */
    public function setDataObject($data)
    {
        $this->dataObject = $data;

        return $this;
    }

    /**
     * Return the data object with the faulty data
     *
     * @return object
     */
    public function getDataObject()
    {
        return $this->dataObject;
    }

    /**
     * Set the type for the given path
     *
     * @param TypeInterface $type
     *
     * @return PropertyValidationException self
     */
    public function setType(TypeInterface $type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Return the data type
     *
     * @return TypeInterface
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set the validator constraint violation
     *
     * @param ConstraintViolationListInterface $violation
     *
     * @return PropertyValidationException self
     */
    public function setConstraintViolation(ConstraintViolationListInterface $violation)
    {
        $this->constraintViolation = $violation;

        return $this;
    }

    /**
     * Return the constraint violation
     *
     * @return ConstraintViolationListInterface
     */
    public function getConstraintViolation()
    {
        return $this->constraintViolation;
    }

    /**
     * Return the exception message
     *
     * @return string
     */
    public function buildMessage()
    {
        $violation = $this->constraintViolation->get(0);

        return sprintf(
            'The value at the path "%s" on resource %s::%s does not comply' .
            ' with the type "%s" (Original error: %s)',
            $this->path,
            $this->definition->getCollection(),
            $this->definition,
            $this->type,
            $violation->getMessage()
        );
    }
}
