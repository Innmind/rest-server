<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Serializer\RequestDecoder;

use Innmind\Rest\Server\Serializer\RequestDecoder;
use Innmind\Http\{
    Message\ServerRequest,
    Message\Form\Parameter,
};
use Innmind\Immutable\MapInterface;

final class Form implements RequestDecoder
{
    public function __invoke(ServerRequest $request): array
    {
        $form = [];

        foreach ($request->form() as $parameter) {
            $form[$parameter->name()] = $this->translate($parameter);
        }

        return $form;
    }

    private function translate(Parameter $parameter)
    {
        if ($parameter->value() instanceof MapInterface) {
            return $parameter
                ->value()
                ->reduce(
                    [],
                    function(array $carry, $key, $value): array {
                        $carry[$key] = $value instanceof Parameter ?
                            $this->translate($value) : $value;

                        return $carry;
                    }
                );
        }

        return $parameter->value();
    }
}
