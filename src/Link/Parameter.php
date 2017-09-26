<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Link;

interface Parameter
{
    public function name(): string;

    /**
     * @return mixed
     */
    public function value();
}
