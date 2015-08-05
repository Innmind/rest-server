<?php

namespace Innmind\Rest\Server\Serializer\Encoder;

use Innmind\Rest\Server\Definition\Resource as Definition;
use Symfony\Component\Serializer\Encoder\EncoderInterface;
use Symfony\Component\Serializer\Encoder\DecoderInterface;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Exception\LogicException;
use Symfony\Component\HttpFoundation\Request;

class JsonEncoder implements EncoderInterface, DecoderInterface
{
    /**
     * {@inheritdoc}
     */
    public function encode($data, $format, array $context = [])
    {
        if (
            !isset($context['definition']) ||
            !$context['definition'] instanceof Definition
        ) {
            throw new LogicException(
                'You need to specify a resource definition ' .
                'in the encoding context'
            );
        }

        $output = [];
        $definition = $context['definition'];

        foreach ($definition->getProperties() as $property) {
            if (
                (
                    $property->getType() === 'resource' ||
                    (
                        $property->getType() === 'array' &&
                        $property->getOption('inner_type') === 'resource'
                    )
                ) &&
                !$property->hasOption('inline')
            ) {
                continue;
            }

            $output[(string) $property] = $data[(string) $property];
        }

        return json_encode($output);
    }

    /**
     * {@inheritdoc}
     */
    public function decode($data, $format, array $context = [])
    {
        if (!$data instanceof Request) {
            throw new InvalidArgumentException(
                'You need to pass the request object ' .
                'in order to decode its content'
            );
        }

        return json_decode($data->getContent(), true);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsEncoding($format)
    {
        return $format === 'json';
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDecoding($format)
    {
        return $format === 'json';
    }
}
