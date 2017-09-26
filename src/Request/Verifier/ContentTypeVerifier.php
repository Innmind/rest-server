<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Request\Verifier;

use Innmind\Rest\Server\{
    Formats,
    Format\MediaType,
    Definition\HttpResource
};
use Innmind\Http\{
    Message\ServerRequest,
    Message\Method,
    Exception\Http\UnsupportedMediaType
};
use Negotiation\{
    Negotiator,
    Accept
};

final class ContentTypeVerifier implements Verifier
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
     * @throws UnsupportedMediaType
     */
    public function verify(
        ServerRequest $request,
        HttpResource $definition
    ) {
        if (
            !$request->headers()->has('Content-Type') ||
            !in_array(
                (string) $request->method(),
                [Method::POST, Method::PUT],
                true
            )
        ) {
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
            throw new UnsupportedMediaType;
        }
    }
}
