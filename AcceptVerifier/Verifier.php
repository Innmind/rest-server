<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\AcceptVerifier;

use Innmind\Rest\Server\{
    Formats,
    Format\MediaType
};
use Innmind\Http\{
    Message\ServerRequestInterface,
    Exception\Http\NotAcceptableException
};
use Negotiation\{
    Negotiator,
    Accept
};

final class Verifier implements VerifierInterface
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
     */
    public function verify(ServerRequestInterface $request)
    {
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
            throw new NotAcceptableException;
        }
    }
}
