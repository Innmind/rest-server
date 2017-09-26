<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Response\HeaderBuilder;

use Innmind\Rest\Server\{
    Definition\HttpResource,
    Formats,
    HttpResource as HttpResourceInterface,
    Identity
};
use Innmind\Http\Message\ServerRequest;
use Innmind\Immutable\MapInterface;

final class GetContentTypeBuilder implements GetBuilder
{
    use ContentTypeBuilder;

    private $formats;

    public function __construct(Formats $formats)
    {
        $this->formats = $formats;
    }

    /**
     * {@inheritdoc}
     */
    public function build(
        HttpResourceInterface $resource,
        ServerRequest $request,
        HttpResource $definition,
        Identity $identity
    ): MapInterface {
        return $this->buildHeaderFrom($this->formats, $request);
    }
}
