<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server;

final class Gateway implements GatewayInterface
{
    private $listAccessor;
    private $accessor;
    private $creator;
    private $updater;
    private $remover;

    public function __construct(
        ResourceListAccessorInterface $listAccessor,
        ResourceAccessorInterface $accessor,
        ResourceCreatorInterface $creator,
        ResourceUpdaterInterface $updater,
        ResourceRemoverInterface $remover
    ) {
        $this->listAccessor = $listAccessor;
        $this->accessor = $accessor;
        $this->creator = $creator;
        $this->updater = $updater;
        $this->remover = $remover;
    }

    public function resourceListAccessor(): ResourceListAccessorInterface
    {
        return $this->listAccessor;
    }

    public function resourceAccessor(): ResourceAccessorInterface
    {
        return $this->accessor;
    }

    public function resourceCreator(): ResourceCreatorInterface
    {
        return $this->creator;
    }

    public function resourceUpdater(): ResourceUpdaterInterface
    {
        return $this->updater;
    }

    public function resourceRemover(): ResourceRemoverInterface
    {
        return $this->remover;
    }
}
