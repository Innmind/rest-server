<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Link;

interface ParameterInterface
{
    public function name(): string;

    /**
     * @return mixed
     */
    public function value();
}
