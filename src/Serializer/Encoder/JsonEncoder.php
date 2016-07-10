<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Serializer\Encoder;

use Innmind\Rest\Server\Exception\InvalidArgumentException;
use Innmind\Http\Message\ServerRequestInterface;
use Symfony\Component\Serializer\Encoder\DecoderInterface;

final class JsonEncoder implements DecoderInterface
{
    /**
     * {@inheritdoc}
     */
    public function decode($data, $format, array $context = [])
    {
        if (!$data instanceof ServerRequestInterface) {
            throw new InvalidArgumentException;
        }

        return json_decode((string) $data->body(), true);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDecoding($format)
    {
        return $format === 'json';
    }
}
