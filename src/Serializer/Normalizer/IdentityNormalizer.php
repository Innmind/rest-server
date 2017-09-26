<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Serializer\Normalizer;

use Innmind\Rest\Server\Identity;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class IdentityNormalizer implements NormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        return ['identity' => $object->value()];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof Identity;
    }
}
