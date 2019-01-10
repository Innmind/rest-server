<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Controller;

use Innmind\Rest\Server\{
    Controller,
    Response\HeaderBuilder\UnlinkBuilder,
    Translator\LinkTranslator,
    Gateway,
    Definition\HttpResource,
    Identity,
    Reference,
    Exception\RouteNotFound,
};
use Innmind\Http\{
    Message\ServerRequest,
    Message\Response,
    Message\StatusCode\StatusCode,
    Headers\Headers,
    Exception\Http\BadRequest,
};
use Innmind\Immutable\MapInterface;

final class Unlink implements Controller
{
    private $gateways;
    private $buildHeader;
    private $translate;

    public function __construct(
        MapInterface $gateways,
        UnlinkBuilder $headerBuilder,
        LinkTranslator $translator
    ) {
        if (
            (string) $gateways->keyType() !== 'string' ||
            (string) $gateways->valueType() !== Gateway::class
        ) {
            throw new \TypeError(sprintf(
                'Argument 1 must be of type MapInterface<string, %s>',
                Gateway::class
            ));
        }

        $this->gateways = $gateways;
        $this->buildHeader = $headerBuilder;
        $this->translate = $translator;
    }

    public function __invoke(
        ServerRequest $request,
        HttpResource $definition,
        Identity $identity = null
    ): Response {
        $from = $definition;

        if (!$request->headers()->has('Link')) {
            throw new BadRequest;
        }

        try {
            $links = ($this->translate)($request->headers()->get('Link'));
        } catch (RouteNotFound $e) {
            throw new BadRequest('', 0, $e);
        }

        $unlink = $this
            ->gateways
            ->get((string) $from->gateway())
            ->resourceUnlinker();
        $unlink(
            $from = new Reference($from, $identity),
            ...$links
        );

        return new Response\Response(
            $code = StatusCode::of('NO_CONTENT'),
            $code->associatedreasonPhrase(),
            $request->protocolVersion(),
            Headers::of(
                ...($this->buildHeader)($request, $from, ...$links)
            )
        );
    }
}
