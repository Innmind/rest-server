<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Exception;

use Innmind\Immutable\Map;

class HttpResourceNormalizationException extends RuntimeException
{
    private Map $errors;

    public function __construct(Map $errors)
    {
        if (
            (string) $errors->keyType() !== 'string' ||
            (string) $errors->valueType() !== NormalizationException::class
        ) {
            throw new \TypeError(sprintf(
                'Argument 1 must be of type Map<string, %s>',
                NormalizationException::class
            ));
        }

        $this->errors = $errors;
        parent::__construct('The input resource is not normalizable');
    }

    /**
     * @return Map<string, DenormalizationException>
     */
    public function errors(): Map
    {
        return $this->errors;
    }
}
