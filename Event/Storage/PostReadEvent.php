<?php

namespace Innmind\Rest\Server\Event\Storage;

use Innmind\Rest\Server\Collection;
use Innmind\Rest\Server\Definition\Resource;

class PostReadEvent extends PreReadEvent
{
    public function __construct(
        Resource $definition,
        $id,
        Collection $resources
    ) {
        parent::__construct($definition, $id);

        $this->resources = $resources;
    }
}
