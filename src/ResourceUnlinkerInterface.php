<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server;

interface ResourceUnlinkerInterface
{
    /**
     * @return void
     */
    public function __invoke(
        HttpResourceInterface $from,
        HttpResourceInterface $to
    );
}
