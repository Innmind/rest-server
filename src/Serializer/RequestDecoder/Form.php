<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Serializer\RequestDecoder;

use Innmind\Rest\Server\Serializer\RequestDecoder;
use Innmind\Http\{
    Message\ServerRequest,
    Message\Form\Parameter,
};

final class Form implements RequestDecoder
{
    public function __invoke(ServerRequest $request): array
    {
        return $request->form()->reduce(
            [],
            static function(array $form, Parameter $parameter): array {
                $form[$parameter->name()] = $parameter->value();

                return $form;
            },
        );
    }
}
