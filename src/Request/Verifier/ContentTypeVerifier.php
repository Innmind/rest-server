<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Request\Verifier;

use Innmind\Rest\Server\{
    Formats,
    Format\MediaType,
    Definition\HttpResource,
};
use Innmind\Http\{
    Message\ServerRequest,
    Message\Method,
    Header\Value,
    Exception\Http\UnsupportedMediaType,
};
use function Innmind\Immutable\join;
use Negotiation\{
    Negotiator,
    Accept,
};

final class ContentTypeVerifier implements Verifier
{
    private Formats $formats;
    private Negotiator $negotiator;

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
    public function __invoke(
        ServerRequest $request,
        HttpResource $definition
    ): void {
        if (
            !$request->headers()->contains('Content-Type') ||
            !\in_array(
                $request->method()->toString(),
                [Method::post()->toString(), Method::put()->toString()],
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
            join(
                ', ',
                $request
                    ->headers()
                    ->get('Content-Type')
                    ->values()
                    ->mapTo(
                        'string',
                        static fn(Value $value): string => $value->toString(),
                    )
            )->toString(),
            $types
        );

        if (!$best instanceof Accept) {
            throw new UnsupportedMediaType;
        }
    }
}
