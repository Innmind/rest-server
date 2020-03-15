<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Serializer\Normalizer;

use Innmind\Rest\Server\{
    HttpResource as Resource,
    HttpResource\Property,
    Definition\Access,
    Exception\NormalizationException,
    Exception\HttpResourceNormalizationException,
};
use Innmind\Immutable\Map;

final class HttpResource
{
    public function __invoke(Resource $resource): array
    {
        /** @var Map<string, NormalizationException> */
        $errors = Map::of('string', NormalizationException::class);

        $definition = $resource->definition();
        $mask = new Access(Access::READ);
        $data = $resource
            ->properties()
            ->filter(static function(string $name, Property $property) use ($definition, $mask): bool {
                return $definition
                    ->properties()
                    ->get($name)
                    ->access()
                    ->matches($mask);
            })
            ->reduce(
                [],
                function(
                    array $data,
                    string $name,
                    Property $property
                ) use (
                    &$errors,
                    $definition,
                    $mask
                ): array {
                    try {
                        /** @psalm-suppress MixedAssignment */
                        $data[$name] = $definition
                            ->properties()
                            ->get($name)
                            ->type()
                            ->normalize($property->value());
                    } catch (NormalizationException $e) {
                        /**
                         * @psalm-suppress MixedMethodCall
                         * @var Map<string, NormalizationException>
                         */
                        $errors = $errors->put($name, $e);
                    }

                    return $data;
                }
            );

        /** @psalm-suppress MixedMethodCall */
        if ($errors->size() > 0) {
            /** @psalm-suppress MixedArgument */
            throw new HttpResourceNormalizationException($errors);
        }

        return ['resource' => $data];
    }
}
