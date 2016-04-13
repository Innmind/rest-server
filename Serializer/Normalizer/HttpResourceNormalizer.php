<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Serializer\Normalizer;

use Innmind\Rest\Server\{
    HttpResource,
    Property,
    Definition\HttpResource as ResourceDefinition,
    Definition\Property as PropertyDefinition,
    Exception\BadMethodCallException,
    Exception\DenormalizationException,
    Exception\NormalizationException,
    Exception\HttpResourceDenormalizationException,
    Exception\HttpResourceNormalizationException
};
use Innmind\Immutable\Map;
use Symfony\Component\Serializer\Normalizer\{
    DenormalizerInterface,
    NormalizerInterface
};

class HttpResourceNormalizer implements NormalizerInterface, DenormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $data = [];
        $errors = new Map('string', NormalizationException::class);

        $definition = $object->definition();
        $object
            ->properties()
            ->foreach(function(
                string $name,
                Property $property
            ) use (
                &$data,
                &$errors,
                $definition
            ) {
                try {
                    $data[$name] = $definition
                        ->properties()
                        ->get($name)
                        ->type()
                        ->normalize($property->value());
                } catch (NormalizationException $e) {
                    $errors = $errors->put($name, $e);
                }
            });

        if ($errors->size() > 0) {
            throw new HttpResourceNormalizationException($errors);
        }

        return ['resource' => $data];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof HttpResource;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        if (
            !isset($context['definition']) ||
            !$context['definition'] instanceof ResourceDefinition
        ) {
            throw new BadMethodCallException(
                'You must give a resource definition'
            );
        }

        $definition = $context['definition'];
        $properties = new Map('string', Property::class);
        $errors = new Map('string', DenormalizationException::class);
        $data = $data['resource'];

        $definition
            ->properties()
            ->foreach(function(
                string $name,
                PropertyDefinition $definition
            ) use (
                &$properties,
                &$errors,
                $data
            ) {
                if (!isset($data[$name])) {
                    if ($definition->isOptional()) {
                        return;
                    }

                    $errors = $errors->put(
                        $name,
                        new DenormalizationException('The field is missing')
                    );

                    return;
                }

                try {
                    $properties = $properties->put(
                        $name,
                        new Property(
                            $name,
                            $definition->type()->denormalize($data[$name])
                        )
                    );
                } catch (DenormalizationException $e) {
                    $errors = $errors->put($name, $e);
                }
            });

        if ($errors->size() > 0) {
            throw new HttpResourceDenormalizationException($errors);
        }

        return new HttpResource($definition, $properties);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return $type === HttpResource::class &&
            is_array($data) &&
            isset($data['resource']);
    }
}
