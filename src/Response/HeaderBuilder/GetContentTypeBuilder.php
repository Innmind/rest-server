<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Response\HeaderBuilder;

use Innmind\Rest\Server\{
    Definition\HttpResource,
    Formats,
    HttpResourceInterface,
    IdentityInterface
};
use Innmind\Http\Message\ServerRequestInterface;
use Innmind\Immutable\MapInterface;

final class GetContentTypeBuilder implements GetBuilderInterface
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
        ServerRequestInterface $request,
        HttpResource $definition,
        IdentityInterface $identity
    ): MapInterface {
        return $this->buildHeaderFrom($this->formats, $request);
    }
}
