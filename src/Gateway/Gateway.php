<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Gateway;

use Innmind\Rest\Server\{
    Gateway as GatewayInterface,
    ResourceListAccessor,
    ResourceAccessor,
    ResourceCreator,
    ResourceUpdater,
    ResourceRemover,
    ResourceLinker,
    ResourceUnlinker
};

final class Gateway implements GatewayInterface
{
    private ResourceListAccessor $listAccessor;
    private ResourceAccessor $accessor;
    private ResourceCreator $creator;
    private ResourceUpdater $updater;
    private ResourceRemover $remover;
    private ResourceLinker $linker;
    private ResourceUnlinker $unlinker;

    public function __construct(
        ResourceListAccessor $listAccessor,
        ResourceAccessor $accessor,
        ResourceCreator $creator,
        ResourceUpdater $updater,
        ResourceRemover $remover,
        ResourceLinker $linker,
        ResourceUnlinker $unlinker
    ) {
        $this->listAccessor = $listAccessor;
        $this->accessor = $accessor;
        $this->creator = $creator;
        $this->updater = $updater;
        $this->remover = $remover;
        $this->linker = $linker;
        $this->unlinker = $unlinker;
    }

    public function resourceListAccessor(): ResourceListAccessor
    {
        return $this->listAccessor;
    }

    public function resourceAccessor(): ResourceAccessor
    {
        return $this->accessor;
    }

    public function resourceCreator(): ResourceCreator
    {
        return $this->creator;
    }

    public function resourceUpdater(): ResourceUpdater
    {
        return $this->updater;
    }

    public function resourceRemover(): ResourceRemover
    {
        return $this->remover;
    }

    public function resourceLinker(): ResourceLinker
    {
        return $this->linker;
    }

    public function resourceUnlinker(): ResourceUnlinker
    {
        return $this->unlinker;
    }
}
