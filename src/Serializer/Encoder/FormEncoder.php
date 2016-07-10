<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Serializer\Encoder;

use Innmind\Rest\Server\Exception\InvalidArgumentException;
use Innmind\Http\{
    Message\ServerRequestInterface,
    Message\Form\ParameterInterface
};
use Innmind\Immutable\{
    Map,
    MapInterface
};
use Symfony\Component\Serializer\Encoder\DecoderInterface;

final class FormEncoder implements DecoderInterface
{
    /**
     * {@inheritdoc}
     */
    public function decode($data, $format, array $context = [])
    {
        if (!$data instanceof ServerRequestInterface) {
            throw new InvalidArgumentException;
        }

        $form = [];

        foreach ($data->form() as $parameter) {
            $form[$parameter->name()] = $this->translate($parameter);
        }

        return $form;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDecoding($format)
    {
        return $format === 'form';
    }

    private function translate(ParameterInterface $parameter)
    {
        if ($parameter->value() instanceof MapInterface) {
            return $parameter
                ->value()
                ->reduce(
                    [],
                    function(array $carry, $key, $value): array {
                        $carry[$key] = $value instanceof ParameterInterface ?
                            $this->translate($value) : $value;

                        return $carry;
                    }
                );
        }

        return $parameter->value();
    }
}
