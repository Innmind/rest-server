<?php

namespace Innmind\Rest\Server\Definition;

use Innmind\Rest\Server\Definition\Type\ArrayType;
use Innmind\Rest\Server\Definition\Type\BooleanType;
use Innmind\Rest\Server\Definition\Type\DateType;
use Innmind\Rest\Server\Definition\Type\FloatType;
use Innmind\Rest\Server\Definition\Type\IntType;
use Innmind\Rest\Server\Definition\Type\StringType;

class Types
{
    protected static $types = null;

    /**
     * Return the wished type
     *
     * @param string $name
     *
     * @throws InvalidArgumentException If the type is unknown
     *
     * @return TypeInterface
     */
    public static function get($name)
    {
        if (!self::has($name)) {
            throw new \InvalidArgumentException(sprintf(
                'Unknown type "%s"',
                $name
            ));
        }

        return self::$types[(string) $name];
    }

    /**
     * Test if the type exists
     *
     * @param string $name
     *
     * @return bool
     */
    public static function has($name)
    {
        if (self::$types === null) {
            self::addDefaults();
        }

        return isset(self::$types[(string) $name]);
    }

    /**
     * Initialize all types
     *
     * @return void
     */
    protected static function addDefaults()
    {
        self::$types = [
            'array' => new ArrayType,
            'bool' => new BooleanType,
            'date' => new DateType,
            'float' => new FloatType,
            'int' => new IntType,
            'string' => new StringType,
        ];
    }
}
