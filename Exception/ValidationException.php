<?php

namespace Innmind\Rest\Server\Exception;

use Symfony\Component\Validator\ConstraintViolationListInterface;

class ValidationException extends \Exception
{
    protected $access;
    protected $violations;

    /**
     * Return an instance of ValidationException
     *
     * @param string $access
     * @param ConstraintViolationListInterface $violations
     *
     * @return ValidationException
     */
    public static function build(
        $access,
        ConstraintViolationListInterface $violations
    ) {
        $ex = new self;
        $ex->access = (string) $access;
        $ex->violations = $violations;

        return $ex;
    }

    /**
     * Return the access used to validate data
     *
     * @return string
     */
    public function getAccess()
    {
        return $this->access;
    }

    /**
     * Return the violations
     *
     * @return ConstraintViolationListInterface
     */
    public function getViolations()
    {
        return $this->violations;
    }
}
