<?php

namespace Innmind\Rest\Server\Serializer\Normalizer;

use Innmind\Rest\Server\Resource;
use Innmind\Rest\Server\Access;
use Innmind\Rest\Server\DEfinition\Resource as Definition;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Exception\LogicException;

class ResourceNormalizer implements NormalizerInterface, DenormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $def = $object->getDefinition();
        $data = [];

        foreach ($object->getProperties() as $key => $value) {
            if ($def->getProperty($key)->hasAccess(Access::READ)) {
                $data[$key] = $value;
            }
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof Resource;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        if (
            !isset($context['definition']) ||
            !$context['definition'] instanceof Definition
        ) {
            throw new LogicException(
                'You need to specify a resource definition ' .
                'in the denormalization context'
            );
        }

        if (
            !isset($context['access']) ||
            !in_array($context['access'], [Access::CREATE, Access::UPDATE])
        ) {
            throw new LogicException(sprintf(
                'You need to specify either "%s" or "%s" access flags ' .
                'in the denormalization context',
                Access::CREATE,
                Access::UPDATE
            ));
        }

        $resource = new Resource;
        $resource->setDefinition($context['definition']);

        foreach ($context['definition']->getProperties() as $prop) {
            if (!$prop->hasAccess($context['access'])) {
                continue;
            }

            if (!isset($data[(string) $prop])) {
                continue;
            }

            $resource->set((string) $prop, $data[(string) $prop]);
        }

        return $resource;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return is_array($data) && $type === Resource::class;
    }
}
