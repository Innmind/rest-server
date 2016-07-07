<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Response\HeaderBuilder;

use Innmind\Rest\Server\{
    Definition\HttpResource,
    Request\Range,
    Formats
};
use Innmind\Http\{
    Message\ServerRequestInterface,
    Header\HeaderInterface,
    Header\ParameterInterface,
    Header\ContentType,
    Header\ContentTypeValue
};
use Innmind\Specification\SpecificationInterface;
use Innmind\Immutable\{
    SetInterface,
    MapInterface,
    Map
};

final class ListContentTypeBuilder implements ListBuilderInterface
{
    private $formats;

    public function __construct(Formats $formats)
    {
        $this->formats = $formats;
    }

    /**
     * {@inheritdoc}
     */
    public function build(
        SetInterface $identities,
        ServerRequestInterface $request,
        HttpResource $definition,
        SpecificationInterface $specification = null,
        Range $range = null
    ): MapInterface {
        $map = new Map('string', HeaderInterface::class);
        $format = $this->formats->matching(
            (string) $request
                ->headers()
                ->get('Accept')
                ->values()
                ->join(', ')
        );

        return $map->put(
            'Content-Type',
            new ContentType(
                new ContentTypeValue(
                    $format->preferredMediaType()->topLevel(),
                    $format->preferredMediaType()->subType(),
                    new Map('string', ParameterInterface::class)
                )
            )
        );
    }
}
