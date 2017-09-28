<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Serializer\Normalizer;

use Innmind\Rest\Server\Identity;
use Innmind\Immutable\SetInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class IdentitiesNormalizer implements NormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        return $object->reduce(
            ['identities' => []],
            function(array $carry, Identity $identity): array {
                $carry['identities'][] = $identity->value();

                return $carry;
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof SetInterface && (string) $data->type() === Identity::class;
    }
}
