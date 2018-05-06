<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Controller;

use Innmind\Rest\Server\{
    Controller,
    Response\HeaderBuilder\LinkBuilder,
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
    Message\ReasonPhrase\ReasonPhrase,
    Headers\Headers,
    Exception\Http\BadRequest,
};
use Innmind\Immutable\MapInterface;

final class Link implements Controller
{
    private $gateways;
    private $buildHeader;
    private $translate;

    public function __construct(
        MapInterface $gateways,
        LinkBuilder $headerBuilder,
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
            $tos = ($this->translate)($request->headers()->get('Link'));
        } catch (RouteNotFound $e) {
            throw new BadRequest('', 0, $e);
        }

        $link = $this
            ->gateways
            ->get((string) $from->gateway())
            ->resourceLinker();
        $link(
            $from = new Reference($from, $identity),
            $tos
        );

        return new Response\Response(
            $code = new StatusCode(StatusCode::codes()->get('NO_CONTENT')),
            new ReasonPhrase(ReasonPhrase::defaults()->get($code->value())),
            $request->protocolVersion(),
            new Headers(
                ($this->buildHeader)($request, $from, $tos)
            )
        );
    }
}
