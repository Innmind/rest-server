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
    Message\StatusCode\StatusCode,
    Headers\Headers,
    Exception\Http\RangeNotSatisfiable,
};
use Innmind\Immutable\MapInterface;

final class Index implements Controller
{
    private $encode;
    private $normalize;
    private $extractRange;
    private $buildSpecification;
    private $gateways;
    private $buildHeader;

    public function __construct(
        Encoder $encode,
        Identities $normalize,
        MapInterface $gateways,
        ListBuilder $headerBuilder,
        Extractor $rangeExtractor,
        Builder $specificationBuilder
    ) {
        if (
            (string) $gateways->keyType() !== 'string' ||
            (string) $gateways->valueType() !== Gateway::class
        ) {
            throw new \TypeError(sprintf(
                'Argument 3 must be of type MapInterface<string, %s>',
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
        if (!is_null($identity)) {
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
            ->get((string) $definition->gateway())
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
                ...($this->buildHeader)(
                    $identities,
                    $request,
                    $definition,
                    $specification,
                    $range
                )
            ),
            ($this->encode)(
                $request,
                ($this->normalize)($identities)
            )
        );
    }
}
