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
    Exception\Http\NotAcceptable,
};
use Negotiation\{
    Negotiator,
    Accept,
};

final class AcceptVerifier implements Verifier
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
     * @throws NotAcceptable
     */
    public function __invoke(
        ServerRequest $request,
        HttpResource $definition
    ): void {
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
                ->get('Accept')
                ->values()
                ->join(', '),
            $types
        );

        if (!$best instanceof Accept) {
            throw new NotAcceptable;
        }
    }
}
