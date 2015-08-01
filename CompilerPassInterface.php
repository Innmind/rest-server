<?php

namespace Innmind\Rest\Server;

interface CompilerPassInterface
{
    /**
     * Process the registry configuration
     *
     * @param Registry $registry
     *
     * @return void
     */
    public function process(Registry $registry);
}
