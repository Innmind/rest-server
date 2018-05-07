<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Definition;

use Innmind\Rest\Server\{
    Definition\Type\SetType,
    Definition\Type\MapType,
    Definition\Type\BoolType,
    Definition\Type\DateType,
    Definition\Type\FloatType,
    Definition\Type\IntType,
    Definition\Type\StringType,
    Exception\DomainException,
};
use Innmind\Immutable\{
    Map,
    MapInterface,
    Sequence,
};

final class Types
{
    private $types;

    public function __construct(string ...$types)
    {
        $types = new Sequence(
            SetType::class,
            MapType::class,
            BoolType::class,
            DateType::class,
            FloatType::class,
            IntType::class,
            StringType::class,
            ...$types
        );
        $this->types = new Map('string', 'string');

        foreach ($types as $type) {
            $this->register($type);
        }
    }

    /**
     * Register the given type
     *
     * @param string $type FQCN
     *
     * @return self
     */
    private function register(string $type): void
    {
        $refl = new \ReflectionClass($type);

        if (!$refl->implementsInterface(Type::class)) {
            throw new DomainException(sprintf(
                'The type "%s" must implement Type',
                $type
            ));
        }

        $this->types = [$type, 'identifiers']()->reduce(
            $this->types,
            static function(MapInterface $types, string $identifier) use ($type): MapInterface {
                return $types->put(
                    $identifier,
                    $type
                );
            }
        );
    }

    /**
     * Return the types mapping
     *
     * @return MapInterface<string, string>
     */
    public function all(): MapInterface
    {
        return $this->types;
    }

    /**
     * Build a new type instance of the wished type
     *
     * @param string $type
     * @param MapInterface<scalar, variable> $config
     *
     * @return Type
     */
    public function build(string $type, MapInterface $config): Type
    {
        if (
            (string) $config->keyType() !== 'scalar' ||
            (string) $config->valueType() !== 'variable'
        ) {
            throw new \TypeError('Argument 2 must be of type MapInterface<scalar, variable>');
        }

        return call_user_func(
            [$this->types->get($type), 'fromConfig'],
            $config,
            $this
        );
    }
}
