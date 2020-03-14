<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Exception;

use Innmind\Immutable\MapInterface;

class HttpResourceDenormalizationException extends RuntimeException
{
    private MapInterface $errors;

    public function __construct(MapInterface $errors)
    {
        if (
            (string) $errors->keyType() !== 'string' ||
            (string) $errors->valueType() !== DenormalizationException::class
        ) {
            throw new \TypeError(sprintf(
                'Argument 1 must be of type MapInterface<string, %s>',
                DenormalizationException::class
            ));
        }

        $this->errors = $errors;
        parent::__construct('The input resource is not denormalizable');
    }

    /**
     * @return MapInterface<string, DenormalizationException>
     */
    public function errors(): MapInterface
    {
        return $this->errors;
    }
}
