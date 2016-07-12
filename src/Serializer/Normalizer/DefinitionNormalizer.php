<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Serializer\Normalizer;

use Innmind\Rest\Server\Definition\{
    HttpResource,
    Property
};
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class DefinitionNormalizer implements NormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        return [
            'identity' => (string) $object->identity(),
            'properties' => $object
                ->properties()
                ->reduce(
                    [],
                    function(array $carry, string $name, Property $property) {
                        $carry[$name] = [
                            'type' => call_user_func([
                                get_class($property->type()),
                                'identifiers'
                            ])
                                ->current(),
                            'access' => $property
                                ->access()
                                ->mask()
                                ->toPrimitive(),
                            'variants' => $property
                                ->variants()
                                ->toPrimitive(),
                            'optional' => $property->isOptional(),
                        ];

                        return $carry;
                    }
                ),
            'metas' => $object->metas()->toPrimitive(),
            'rangeable' => $object->isRangeable(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof HttpResource;
    }
}