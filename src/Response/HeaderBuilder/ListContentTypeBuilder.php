<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Response\HeaderBuilder;

use Innmind\Rest\Server\{
    Definition\HttpResource,
    Request\Range,
    Formats,
};
use Innmind\Http\Message\ServerRequest;
use Innmind\Specification\Specification;
use Innmind\Immutable\SetInterface;

final class ListContentTypeBuilder implements ListBuilder
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
    public function __invoke(
        SetInterface $identities,
        ServerRequest $request,
        HttpResource $definition,
        Specification $specification = null,
        Range $range = null
    ): SetInterface {
        return $this->buildHeaderFrom($this->formats, $request);
    }
}
