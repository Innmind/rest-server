<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Controller;

use Innmind\Rest\Server\{
    Controller,
    Identity,
    Definition\HttpResource,
    Gateway,
    Response\HeaderBuilder\ListBuilder,
    RangeExtractor\Extractor,
    SpecificationBuilder\Builder,
    Request\Range,
    Serializer\Encoder,
    Serializer\Normalizer\Identities,
    Exception\RangeNotFound,
    Exception\NoFilterFound,
    Exception\LogicException,
};
use Innmind\Http\{
    Message\ServerRequest,
    Message\Response,
    Message\StatusCode,
    Headers,
    Exception\Http\RangeNotSatisfiable,
};
use Innmind\Immutable\Map;
use function Innmind\Immutable\unwrap;

final class Index implements Controller
{
    private Encoder $encode;
    private Identities $normalize;
    private Extractor $extractRange;
    private Builder $buildSpecification;
    /** @var Map<string, Gateway> */
    private Map $gateways;
    private ListBuilder $buildHeader;

    /**
     * @param Map<string, Gateway> $gateways
     */
    public function __construct(
        Encoder $encode,
        Identities $normalize,
        Map $gateways,
        ListBuilder $headerBuilder,
        Extractor $rangeExtractor,
        Builder $specificationBuilder
    ) {
        if (
            $gateways->keyType() !== 'string' ||
            $gateways->valueType() !== Gateway::class
        ) {
            throw new \TypeError(\sprintf(
                'Argument 3 must be of type Map<string, %s>',
                Gateway::class
            ));
        }

        $this->encode = $encode;
        $this->normalize = $normalize;
        $this->extractRange = $rangeExtractor;
        $this->buildSpecification = $specificationBuilder;
        $this->gateways = $gateways;
        $this->buildHeader = $headerBuilder;
    }

    public function __invoke(
        ServerRequest $request,
        HttpResource $definition,
        Identity $identity = null
    ): Response {
        if (!\is_null($identity)) {
            throw new LogicException;
        }

        try {
            $range = ($this->extractRange)($request);
        } catch (RangeNotFound $e) {
            $range = null;
        }

        try {
            $specification = ($this->buildSpecification)($request, $definition);
        } catch (NoFilterFound $e) {
            $specification = null;
        }

        $access = $this
            ->gateways
            ->get($definition->gateway()->toString())
            ->resourceListAccessor();
        $identities = $access($definition, $specification, $range);

        if (
            $identities->size() === 0 &&
            $range instanceof Range
        ) {
            throw new RangeNotSatisfiable;
        }

        return new Response\Response(
            $code = StatusCode::of(
                $range instanceof Range ? 'PARTIAL_CONTENT' : 'OK'
            ),
            $code->associatedreasonPhrase(),
            $request->protocolVersion(),
            Headers::of(
                ...unwrap(($this->buildHeader)(
                    $identities,
                    $request,
                    $definition,
                    $specification,
                    $range
                ))
            ),
            ($this->encode)(
                $request,
                ($this->normalize)($identities)
            )
        );
    }
}
