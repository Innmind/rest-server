<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server;

use Innmind\Rest\Server\{
    Format\Format as FormatFormat,
    Format\MediaType
};
use Innmind\Http\Message\ServerRequest;
use Negotiation\Negotiator;

final class Format
{
    private $accept;
    private $contentType;
    private $negotiator;

    public function __construct(
        Formats $accept,
        Formats $contentType
    ) {
        $this->accept = $accept;
        $this->contentType = $contentType;
        $this->negotiator = new Negotiator;
    }

    public function acceptable(ServerRequest $request): FormatFormat
    {
        return $this->accept->matching(
            (string) $request
                ->headers()
                ->get('Accept')
                ->values()
                ->join(', ')
        );
    }

    public function contentType(ServerRequest $request): FormatFormat
    {
        return $this->contentType->matching(
            (string) $request
                ->headers()
                ->get('Content-Type')
                ->values()
                ->current()
        );
    }
}
