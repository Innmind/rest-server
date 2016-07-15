<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Exception;

use Innmind\Immutable\MapInterface;

class HttpResourceNormalizationException extends RuntimeException
{
    private $errors;

    public function __construct(MapInterface $errors)
    {
        if (
            (string) $errors->keyType() !== 'string' ||
            (string) $errors->valueType() !== NormalizationException::class
        ) {
            throw new InvalidArgumentException;
        }

        $this->errors = $errors;
        parent::__construct('The input resource is not normalizable');
    }

    /**
     * @return MapInterface<string, DenormalizationException>
     */
    public function errors(): MapInterface
    {
        return $this->errors;
    }
}
