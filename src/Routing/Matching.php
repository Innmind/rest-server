<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Routing;

use Innmind\Rest\Server\{
    Definition\HttpResource,
    Identity,
};

final class Matching
{
    private HttpResource $definition;
    private ?Identity $identity;

    public function __construct(HttpResource $definition, Identity $identity = null)
    {
        $this->definition = $definition;
        $this->identity = $identity;
    }

    public function definition(): HttpResource
    {
        return $this->definition;
    }

    public function identity(): ?Identity
    {
        return $this->identity;
    }
}
