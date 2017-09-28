<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Link\Parameter;

use Innmind\Rest\Server\Link\Parameter as ParameterInterface;

final class Parameter implements ParameterInterface
{
    private $name;
    private $value;

    public function __construct(string $name, $value)
    {
        $this->name = $name;
        $this->value = $value;
    }

    public function name(): string
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function value()
    {
        return $this->value;
    }
}
