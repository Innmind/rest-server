<?php

namespace Innmind\Rest\Server\Serializer\Normalizer;

use Innmind\Rest\Server\Resource;
use Innmind\Rest\Server\Collection;
use Innmind\Rest\Server\Access;
use Innmind\Rest\Server\ResourceBuilder;
use Innmind\Rest\Server\Definition\Resource as Definition;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Exception\LogicException;
use Symfony\Component\Serializer\Exception\UnsupportedException;

class ResourceNormalizer implements NormalizerInterface, DenormalizerInterface
{
    protected $resourceBuilder;

    public function __construct(ResourceBuilder $resourceBuilder)
    {
        $this->resourceBuilder = $resourceBuilder;
    }

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

        $definition = $context['definition'];

        if (isset($data['resource'])) {
            return $this->createResource($data['resource'], $definition);
        } else if (isset($data['resources'])) {
            $resources = new Collection;

            foreach ($data['resources'] as $data) {
                $resources[] = $this->createResource($data, $definition);
            }

            $resources->rewind();

            return $resources;
        }

        throw new UnsupportedException(
            'Data must be set under the key "resource" or "resources"'
        );
    }

    /**
     * Create a resource off of the given array and definition
     *
     * @param array $data
     * @param Definition $definition
     *
     * @return Resource
     */
    protected function createResource(array $data, Definition $definition)
    {
        return $this->resourceBuilder->build(
            $this->transformArrayToObject($data, $definition),
            $definition
        );
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return is_array($data) && $type === Resource::class;
    }

    /**
     * Transform an array into an object (reursively)
     *
     * @param array $data
     * @param Definition $definition
     *
     * @return StdClass
     */
    protected function transformArrayToObject(
        array $data,
        Definition $definition
    ) {
        $object = new \stdClass;

        foreach ($data as $key => $value) {
            if (!$definition->hasProperty($key)) {
                continue;
            }

            $prop = $definition->getProperty($key);

            if ($prop->containsResource()) {
                if ($prop->getType() === 'array') {
                    $coll = [];
                    foreach ($value as $subValue) {
                        $coll[] = $this->transformArrayToObject(
                            $subValue,
                            $prop->getOption('resource')
                        );
                    }
                    $object->$key = $coll;
                } else {
                    $object->$key = $this->transformArrayToObject(
                        $value,
                        $prop->getOption('resource')
                    );
                }
            } else {
                $object->$key = $value;
            }
        }

        return $object;
    }
}
