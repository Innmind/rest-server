<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server;

use Innmind\Rest\Server\{
    Definition\Directory,
    Routing\Prefix,
    RangeExtractor\Extractor,
    RangeExtractor\DelegationExtractor,
    RangeExtractor\HeaderExtractor,
    RangeExtractor\QueryExtractor,
    SpecificationBuilder\Builder,
    Request\Verifier\Verifier,
    Request\Verifier\DelegationVerifier,
    Request\Verifier\AcceptVerifier,
    Request\Verifier\ContentTypeVerifier,
    Request\Verifier\RangeVerifier,
    Serializer\RequestDecoder,
    Serializer\Encoder,
    Serializer\Normalizer,
    Serializer\Denormalizer,
    Definition\Locator,
    Routing\Routes,
    Response\HeaderBuilder,
    Controller\CatchHttpException,
    Controller\Verify,
    Controller\CatchActionNotImplemented,
    Controller\CatchHttpResourceDenormalizationException,
    Controller\CatchFilterNotApplicable,
    Controller\Create,
    Controller\Get,
    Controller\Index,
    Controller\Options,
    Controller\Remove,
    Controller\Update,
    Controller\Link,
    Controller\Unlink,
    Controller\Capabilities,
    Translator\LinkTranslator,
};
use Innmind\Immutable\Map;

/**
 * @param Map<string, Gateway> $gateways
 *
 * @return array{routes: Routes, controller: array{create: Controller, get: Controller, index: Controller, options: Controller, remove: Controller, update: Controller, link: Controller, unlink: Controller, capabilities: Capabilities}, locator: Locator}
 */
function bootstrap(
    Map $gateways,
    Directory $directory,
    Formats $acceptFormats = null,
    Formats $contentTypeFormats = null,
    Prefix $prefix = null,
    Extractor $rangeExtractor = null,
    Builder $specificationBuilder = null,
    Verifier $requestVerifier = null,
    RequestDecoder $requestDecoder = null,
    Encoder $encoder = null
): array {
    $acceptFormats = $acceptFormats ?? AcceptFormats::default();
    $contentTypeFormats = $contentTypeFormats ?? ContentTypeFormats::default();
    $format = new Format($acceptFormats, $contentTypeFormats);
    $rangeExtractor = $rangeExtractor ?? new DelegationExtractor(
        new HeaderExtractor,
        new QueryExtractor
    );
    $specificationBuilder = $specificationBuilder ?? new Builder\Builder;
    $requestVerifier = $requestVerifier ?? new DelegationVerifier(
        new AcceptVerifier($acceptFormats),
        new ContentTypeVerifier($contentTypeFormats),
        new RangeVerifier
    );
    /**
     * @psalm-suppress InvalidScalarArgument
     * @psalm-suppress InvalidArgument
     */
    $requestDecoder = $requestDecoder ?? new RequestDecoder\Delegate(
        $format,
        Map::of('string', RequestDecoder::class)
            ('json', new RequestDecoder\Json)
            ('form', new RequestDecoder\Form)
    );
    /**
     * @psalm-suppress InvalidScalarArgument
     * @psalm-suppress InvalidArgument
     */
    $encoder = $encoder ?? new Encoder\Delegate(
        $format,
        Map::of('string', Encoder::class)
            ('json', new Encoder\Json)
    );

    $routes = Routes::from($directory);
    $router = new Router($routes, $prefix);

    $catchHttpException = static function(Controller $controller): Controller {
        return new CatchHttpException($controller);
    };
    $verify = static function(Controller $controller) use ($requestVerifier): Controller {
        return new Verify($requestVerifier, $controller);
    };
    $catchActionNotImplemented = static function(Controller $controller): Controller {
        return new CatchActionNotImplemented($controller);
    };
    $catchHttpResourceDenormalizationException = static function(Controller $controller): Controller {
        return new CatchHttpResourceDenormalizationException($controller);
    };

    $linkTranslator = new LinkTranslator($router);
    $locator = new Locator($directory);

    return [
        'routes' => $routes,
        'controller' => [
            'create' => $catchHttpException(
                $verify(
                    $catchActionNotImplemented(
                        $catchHttpResourceDenormalizationException(
                            new Create(
                                $requestDecoder,
                                $encoder,
                                new Normalizer\Identity,
                                new Denormalizer\HttpResource,
                                $gateways,
                                new HeaderBuilder\CreateDelegationBuilder(
                                    new HeaderBuilder\CreateContentTypeBuilder($acceptFormats),
                                    new HeaderBuilder\CreateLocationBuilder($router)
                                )
                            )
                        )
                    )
                )
            ),
            'get' => $catchHttpException(
                $verify(
                    $catchActionNotImplemented(
                        new Get(
                            $encoder,
                            new Normalizer\HttpResource,
                            $gateways,
                            new HeaderBuilder\GetDelegationBuilder(
                                new HeaderBuilder\GetContentTypeBuilder($acceptFormats)
                            )
                        )
                    )
                )
            ),
            'index' => $catchHttpException(
                $verify(
                    $catchActionNotImplemented(
                        new CatchFilterNotApplicable(
                            new Index(
                                $encoder,
                                new Normalizer\Identities,
                                $gateways,
                                new HeaderBuilder\ListDelegationBuilder(
                                    new HeaderBuilder\ListContentTypeBuilder($acceptFormats),
                                    new HeaderBuilder\ListLinksBuilder($router),
                                    new HeaderBuilder\ListRangeBuilder
                                ),
                                $rangeExtractor,
                                $specificationBuilder
                            )
                        )
                    )
                )
            ),
            'options' => $catchHttpException(
                $verify(
                    $catchActionNotImplemented(
                        new Options(
                            $encoder,
                            $format,
                            new Normalizer\Definition
                        )
                    )
                )
            ),
            'remove' => $catchHttpException(
                $verify(
                    $catchActionNotImplemented(
                        new Remove(
                            $gateways,
                            new HeaderBuilder\RemoveDelegationBuilder
                        )
                    )
                )
            ),
            'update' => $catchHttpException(
                $verify(
                    $catchActionNotImplemented(
                        $catchHttpResourceDenormalizationException(
                            new Update(
                                $requestDecoder,
                                $format,
                                new Denormalizer\HttpResource,
                                $gateways,
                                new HeaderBuilder\UpdateDelegationBuilder
                            )
                        )
                    )
                )
            ),
            'link' => $catchHttpException(
                $verify(
                    $catchActionNotImplemented(
                        new Link(
                            $gateways,
                            new HeaderBuilder\LinkDelegationBuilder,
                            $linkTranslator,
                            $locator
                        )
                    )
                )
            ),
            'unlink' => $catchHttpException(
                $verify(
                    $catchActionNotImplemented(
                        new Unlink(
                            $gateways,
                            new HeaderBuilder\UnlinkDelegationBuilder,
                            $linkTranslator,
                            $locator
                        )
                    )
                )
            ),
            'capabilities' => new Capabilities($routes, $router),
        ],
        'locator' => $locator,
    ];
}
