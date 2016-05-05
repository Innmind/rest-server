<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server;

interface GatewayInterface
{
    public function resourceListAccessor(): ResourceListAccessorInterface;
    public function resourceAccessor(): ResourceAccessorInterface;
    public function resourceCreator(): ResourceCreatorInterface;
    public function resourceUpdater(): ResourceUpdaterInterface;
    public function resourceRemover(): ResourceRemoverInterface;
}
