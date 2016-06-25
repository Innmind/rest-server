<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server;

interface ResourceLinkerInterface
{
    public function __invoke(
        HttpResourceInterface $from,
        HttpResourceInterface $to
    ): HttpResourceInterface;
}
