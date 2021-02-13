<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Controller;

use Innmind\Rest\Server\{
    Controller,
    Response\HeaderBuilder\LinkBuilder,
    Translator\LinkTranslator,
    Gateway,
    Definition\HttpResource,
    Definition\Locator,
    Identity,
    Reference,
    Exception\RouteNotFound,
};
use Innmind\Http\{
    Message\ServerRequest,
    Message\Response,
    Message\StatusCode,
    Headers,
    Header\Link as LinkHeader,
    Exception\Http\BadRequest,
};
use Innmind\Immutable\Map;
use function Innmind\Immutable\unwrap;

final class Link implements Controller
{
    /** @var Map<string, Gateway> */
    private Map $gateways;
    private LinkBuilder $buildHeader;
    private LinkTranslator $translate;
    private Locator $locator;

    /**
     * @param Map<string, Gateway> $gateways
     */
    public function __construct(
        Map $gateways,
        LinkBuilder $headerBuilder,
        LinkTranslator $translator,
        Locator $locator
    ) {
        if (
            $gateways->keyType() !== 'string' ||
            $gateways->valueType() !== Gateway::class
        ) {
            throw new \TypeError(sprintf(
                'Argument 1 must be of type Map<string, %s>',
                Gateway::class
            ));
        }

        $this->gateways = $gateways;
        $this->buildHeader = $headerBuilder;
        $this->translate = $translator;
        $this->locator = $locator;
    }

    public function __invoke(
        ServerRequest $request,
        HttpResource $definition,
        Identity $identity = null
    ): Response {
        $fromDefinition = $definition;

        if (!$request->headers()->contains('Link')) {
            throw new BadRequest;
        }

        $link = $request->headers()->get('Link');

        /** @psalm-suppress RedundantCondition */
        if (!$link instanceof LinkHeader) {
            throw new BadRequest;
        }

        try {
            $links = unwrap(($this->translate)($link));
        } catch (RouteNotFound $e) {
            throw new BadRequest('', 0, $e);
        }

        if (!$fromDefinition->accept($this->locator, ...$links)) {
            throw new BadRequest;
        }

        $link = $this
            ->gateways
            ->get($fromDefinition->gateway()->toString())
            ->resourceLinker();
        /** @psalm-suppress PossiblyNullArgument */
        $link(
            $from = new Reference($fromDefinition, $identity),
            ...$links
        );

        return new Response\Response(
            $code = StatusCode::of('NO_CONTENT'),
            $code->associatedreasonPhrase(),
            $request->protocolVersion(),
            Headers::of(
                ...unwrap(($this->buildHeader)($request, $from, ...$links))
            )
        );
    }
}
