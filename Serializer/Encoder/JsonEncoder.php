<?php

namespace Innmind\Rest\Server\Serializer\Encoder;

use Symfony\Component\Serializer\Encoder\EncoderInterface;
use Symfony\Component\Serializer\Encoder\DecoderInterface;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\HttpFoundation\Request;

class JsonEncoder implements EncoderInterface, DecoderInterface
{
    /**
     * {@inheritdoc}
     */
    public function encode($data, $format, array $context = [])
    {
        return json_encode($data);
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
