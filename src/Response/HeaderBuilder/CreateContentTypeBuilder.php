<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Response\HeaderBuilder;

use Innmind\Rest\Server\{
    Definition\HttpResource,
    Formats,
    HttpResourceInterface,
    IdentityInterface
};
use Innmind\Http\Message\ServerRequest;
use Innmind\Immutable\MapInterface;

final class CreateContentTypeBuilder implements CreateBuilderInterface
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
        IdentityInterface $identity,
        ServerRequest $request,
        HttpResource $definition,
        HttpResourceInterface $resource
    ): MapInterface {
        return $this->buildHeaderFrom($this->formats, $request);
    }
}
