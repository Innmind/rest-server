<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Definition;

use Innmind\Immutable\{
    MapInterface,
    SetInterface
};

interface Type
{
    /**
     * Build the type out of the given config
     *
     * @param MapInterface<scalar, variable> $config
     */
    public static function fromConfig(MapInterface $config, Types $types): self;

    /**
     * Transform the data received via http to a data understandable for php
     *
     * @param mixed $data
     *
     * @throws DenormalizationException
     *
     * @return mixed
     */
    public function denormalize($data);

    /**
     * Transform the php data to something serializable
     *
     * @param mixed $data
     *
     * @throws NormalizationException
     *
     * @return mixed
     */
    public function normalize($data);

    /**
     * Return the identifiers that can be used to reference the type
     *
     * @return SetInterface<string>
     */
    public static function identifiers(): SetInterface;

    public function __toString(): string;
}
