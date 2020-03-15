<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Response\HeaderBuilder;

use Innmind\Rest\Server\{
    Reference,
    Link,
};
use Innmind\Http\Message\ServerRequest;
use Innmind\Immutable\Set;

interface LinkBuilder
{
    /**
     * @return Set<Header>
     */
    public function __invoke(
        ServerRequest $request,
        Reference $from,
        Link ...$links
    ): Set;
}
