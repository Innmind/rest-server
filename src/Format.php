<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server;

use Innmind\Rest\Server\{
    Format\Format as FormatFormat,
    Format\MediaType,
};
use Innmind\Http\{
    Message\ServerRequest,
    Header\Value,
};
use function Innmind\Immutable\{
    first,
    join,
};
use Negotiation\Negotiator;

final class Format
{
    private Formats $accept;
    private Formats $contentType;
    private Negotiator $negotiator;

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
        $values = $request
            ->headers()
            ->get('Accept')
            ->values()
            ->mapTo(
                'string',
                static fn(Value $value): string => $value->toString(),
            );

        return $this->accept->matching(
            join(', ', $values)->toString(),
        );
    }

    public function contentType(ServerRequest $request): FormatFormat
    {
        return $this->contentType->matching(
            first($request->headers()->get('Content-Type')->values())->toString(),
        );
    }
}
