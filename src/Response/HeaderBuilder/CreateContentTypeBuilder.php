<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Response\HeaderBuilder;

use Innmind\Rest\Server\{
    Definition\HttpResource,
    Formats,
    HttpResource as HttpResourceInterface,
    Identity,
};
use Innmind\Http\Message\ServerRequest;
use Innmind\Immutable\Set;

final class CreateContentTypeBuilder implements CreateBuilder
{
    use ContentTypeBuilder;

    private Formats $formats;

    public function __construct(Formats $formats)
    {
        $this->formats = $formats;
    }

    public function __invoke(
        Identity $identity,
        ServerRequest $request,
        HttpResource $definition,
        HttpResourceInterface $resource
    ): Set {
        return $this->buildHeaderFrom($this->formats, $request);
    }
}
