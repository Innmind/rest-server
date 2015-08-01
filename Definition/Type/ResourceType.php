<?php

namespace Innmind\Rest\Server\Definition\Type;

use Innmind\Rest\Server\Definition\TypeInterface;
use Innmind\Rest\Server\Definition\Property;
use Innmind\Rest\Server\Definition\Types;
use Symfony\Component\Validator\Constraints\Collection;

class ResourceType implements TypeInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConstraints(Property $property)
    {
        $properties = $property->getOption('resource')->getProperties();
        $fields = [];

        foreach ($properties as $p) {
            $fields[(string) $p] = Types::get($p->getType())->getConstraints($p);
        }

        return [new Collection(['fields' => $fields])];
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return 'resource';
    }
}
