<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server;

interface Gateway
{
    public function resourceListAccessor(): ResourceListAccessor;
    public function resourceAccessor(): ResourceAccessor;
    public function resourceCreator(): ResourceCreator;
    public function resourceUpdater(): ResourceUpdater;
    public function resourceRemover(): ResourceRemover;
    public function resourceLinker(): ResourceLinker;
    public function resourceUnlinker(): ResourceUnlinker;
}
