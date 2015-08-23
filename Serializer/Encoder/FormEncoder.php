<?php

namespace Innmind\Rest\Server\Serializer\Encoder;

use Symfony\Component\Serializer\Encoder\DecoderInterface;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\HttpFoundation\Request;

class FormEncoder implements DecoderInterface
{
    /**
     * {@inheritdoc}
     */
    public function decode($data, $format, array $context = [])
    {
        if (!$data instanceof Request) {
            throw new InvalidArgumentException(
                'You need to pass the request object in order to decode its content'
            );
        }

        return $data->request->all();
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDecoding($format)
    {
        return $format === 'form';
    }
}
