<?php

namespace Innmind\Rest\Server\Request;

use Innmind\Rest\Server\Definition\Resource as ResourceDefinition;
use Innmind\Rest\Server\Formats;
use Innmind\Rest\Server\Exception\PayloadException;
use Symfony\Component\HttpFoundation\Request as HttpRequest;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\Exception\ExceptionInterface;
use Negotiation\Negotiator;

class Parser
{
    protected $serializer;
    protected $formats;
    protected $negotiator;

    public function __construct(
        Serializer $serializer,
        Formats $formats,
        Negotiator $negotiator
    ) {
        $this->serializer = $serializer;
        $this->formats = $formats;
        $this->negotiator = $negotiator;
    }

    /**
     * Check if the request content type is known
     *
     * @param HttpRequest $request
     *
     * @return bool
     */
    public function isContentTypeAcceptable(HttpRequest $request)
    {
        $contentType = $request->headers->get('Content-Type');

        if (!$this->formats->has($contentType)) {
            return false;
        }

        $format = $this->formats->getName($contentType);

        if (!$this->serializer->supportsDecoding($format)) {
            return false;
        }

        return true;
    }

    /**
     * Check if the wished accepted types for the client are supported
     *
     * @param HttpRequest $request
     *
     * @return bool
     */
    public function isRequestedTypeAcceptable(HttpRequest $request)
    {
        $wished = $this->negotiator->getBest(
            $request->headers->get('Accept'),
            $this->formats->getMediaTypes()
        );

        if ($wished === null) {
            return false;
        }

        $format = $this->formats->getName($wished->getValue());

        return $this->serializer->supportsEncoding($format);
    }

    /**
     * Return the data payload as an object
     *
     * @param HttpRequest $request
     * @param ResourceDefinition $definition
     *
     * @return object|array
     */
    public function getData(
        HttpRequest $request,
        ResourceDefinition $definition
    ) {
        $format = $this->formats->getName(
            $request->headers->get('Content-Type')
        );
        $payload = $this->serializer->decode($request, $format);

        if (isset($payload['resource'])) {
            return $this->computePayload($payload['resource'], $definition);
        } else if (isset($payload['resources'])) {
            $objects = [];

            foreach ($payload['resources'] as $object) {
                $objects[] = $this->computePayload($object, $definition);
            }

            return $objects;
        }

        throw new PayloadException(
            'The payload must be set either under ' .
            'the key "resource" or "resources"'
        );
    }

    /**
     * Tranform the array payload into an object
     * and validate it via an option resolver
     *
     * @param array $payload
     * @param ResourceDefinition $definition
     *
     * @throws PayloadException If a field is unknown or misconfigured
     *
     * @return \stdClass
     */
    protected function computePayload(
        array $payload,
        ResourceDefinition $definition
    ) {
        try {
            $this->validatePayload($payload, $definition);
        } catch (ExceptionInterface $e) {
            throw new PayloadException('Bad request payload', 0, $e);
        }

        return $this->transformArrayToObject($payload, $definition);
    }

    /**
     * Make sure the payload match the resource definition
     *
     * @param array $payload
     * @param ResourceDefinition $definition
     *
     * @return void
     */
    protected function validatePayload(
        array $payload,
        ResourceDefinition $definition
    ) {
        $resolver = new OptionsResolver;

        foreach ($definition->getProperties() as $property) {
            $resolver->setRequired((string) $property);

            if ($property->getType() === 'resource') {
                $resolver->addAllowedTypes((string) $property, 'array');
            } else {
                $resolver->addAllowedTypes(
                    (string) $property,
                    $property->getType()
                );
            }
        }

        $payload = $resolver->resolve($payload);

        foreach ($definition->getProperties() as $property) {
            if ($property->getType() === 'resource') {
                $this->validatePayload(
                    $payload[(string) $property],
                    $property->getOption('resource')
                );
            } else if (
                $property->getType() === 'array' &&
                $property->getOption('inner_type') === 'resource'
            ) {
                foreach ($payload[(string) $property] as $subValue) {
                    $this->validatePayload(
                        $subValue,
                        $property->getOption('resource')
                    );
                }
            }
        }
    }

    /**
     * Transform an array to an object
     *
     * @param array $data
     * @param ResourceDefinition $definition
     *
     * @return \stdClass
     */
    protected function transformArrayToObject(
        array $data,
        ResourceDefinition $definition
    ) {
        $o = new \stdClass;

        foreach ($data as $key => $value) {
            $property = $definition->getProperty($key);

            if (
                $property->getType() === 'array' &&
                $property->getOption('inner_type') === 'resource'
            ) {
                $collection = [];

                foreach ($value as $subValue) {
                    $collection[] = $this->transformArrayToObject(
                        $subValue,
                        $property->getOption('resource')
                    );
                }

                $o->$key = $collection;
            } else if ($property->getType() === 'resource') {
                $o->$key = $this->transformArrayToObject(
                    $value,
                    $property->getOption('resource')
                );
            } else {
                $o->$key = $value;
            }
        }

        return $o;
    }
}
