<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Serializer\Normalizer;

use Innmind\Rest\Server\{
    HttpResource,
    Property,
    Definition\HttpResource as ResourceDefinition,
    Definition\Property as PropertyDefinition,
    Definition\Access,
    Exception\BadMethodCallException,
    Exception\DenormalizationException,
    Exception\NormalizationException,
    Exception\HttpResourceDenormalizationException,
    Exception\HttpResourceNormalizationException
};
use Innmind\Immutable\{
    Map,
    Set
};
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
        $mask = new Access((new Set('string'))->add(Access::READ));
        $object
            ->properties()
            ->foreach(function(
                string $name,
                Property $property
            ) use (
                &$data,
                &$errors,
                $definition,
                $mask
            ) {
                $propertyDefinition = $definition
                    ->properties()
                    ->get($name);

                if (!$propertyDefinition->access()->matches($mask)) {
                    return;
                }

                try {
                    $data[$name] = $propertyDefinition
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

        if (
            !isset($context['mask']) ||
            !$context['mask'] instanceof Access
        ) {
            throw new BadMethodCallException('You must give an access mask');
        }

        $definition = $context['definition'];
        $mask = $context['mask'];
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
                $data,
                $mask
            ) {
                if (
                    !$definition->access()->matches($mask) &&
                    isset($data[$name])
                ) {
                    $errors = $errors->put(
                        $name,
                        new DenormalizationException('The field is not allowed')
                    );

                    return;
                }

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
