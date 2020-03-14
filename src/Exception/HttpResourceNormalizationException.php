<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Exception;

use Innmind\Immutable\MapInterface;

class HttpResourceNormalizationException extends RuntimeException
{
    private MapInterface $errors;

    public function __construct(MapInterface $errors)
    {
        if (
            (string) $errors->keyType() !== 'string' ||
            (string) $errors->valueType() !== NormalizationException::class
        ) {
            throw new \TypeError(sprintf(
                'Argument 1 must be of type MapInterface<string, %s>',
                NormalizationException::class
            ));
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
