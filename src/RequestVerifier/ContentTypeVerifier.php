<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\RequestVerifier;

use Innmind\Rest\Server\{
    Formats,
    Format\MediaType,
    Definition\HttpResource
};
use Innmind\Http\{
    Message\ServerRequestInterface,
    Exception\Http\UnsupportedMediaTypeException
};
use Negotiation\{
    Negotiator,
    Accept
};

final class ContentTypeVerifier implements VerifierInterface
{
    private $formats;
    private $negotiator;

    public function __construct(Formats $formats)
    {
        $this->formats = $formats;
        $this->negotiator = new Negotiator;
    }

    /**
     * {@inheritdoc}
     *
     * @throws UnsupportedMediaTypeException
     */
    public function verify(
        ServerRequestInterface $request,
        HttpResource $definition
    ) {
        if (!$request->headers()->has('Content-Type')) {
            return;
        }

        $types = $this
            ->formats
            ->mediaTypes()
            ->reduce(
                [],
                function(array $carry, MediaType $type) {
                    $carry[] = (string) $type;

                    return $carry;
                }
            );
        $best = $this->negotiator->getBest(
            (string) $request
                ->headers()
                ->get('Content-Type')
                ->values()
                ->join(', '),
            $types
        );

        if (!$best instanceof Accept) {
            throw new UnsupportedMediaTypeException;
        }
    }
}