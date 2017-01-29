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
                            'type' => (string) $property->type(),
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
            'metas' => array_combine(
                $object->metas()->keys()->toPrimitive(),
                $object->metas()->values()->toPrimitive()
            ),
            'rangeable' => $object->isRangeable(),
            'linkable_to' => $object
                ->allowedLinks()
                ->reduce(
                    [],
                    function(array $carry, string $type, string $path): array {
                        $carry[$type] = $path;

                        return $carry;
                    }
                ),
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
