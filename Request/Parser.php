<?php

namespace Innmind\Rest\Server\Request;

use Innmind\Rest\Server\Definition\ResourceDefinition;
use Innmind\Rest\Server\Formats;
use Innmind\Rest\Server\HttpResourceInterface;
use Symfony\Component\HttpFoundation\Request as HttpRequest;
use Symfony\Component\Serializer\Serializer;
use Negotiation\Negotiator;

class Parser
{
    protected $serializer;
    protected $formats;
    protected $negotiator;

    public function __construct(
        Serializer $serializer,
        Formats $formats,
        Negotiator $negotiator
    ) {
        $this->serializer = $serializer;
        $this->formats = $formats;
        $this->negotiator = $negotiator;
    }

    /**
     * Check if the request content type is known
     *
     * @param HttpRequest $request
     *
     * @return bool
     */
    public function isContentTypeAcceptable(HttpRequest $request)
    {
        $contentType = $request->headers->get('Content-Type');

        if (!$this->formats->has($contentType)) {
            return false;
        }

        $format = $this->formats->getName($contentType);

        if (!$this->serializer->supportsDecoding($format)) {
            return false;
        }

        return true;
    }

    /**
     * Check if the wished accepted types for the client are supported
     *
     * @param HttpRequest $request
     *
     * @return bool
     */
    public function isRequestedTypeAcceptable(HttpRequest $request)
    {
        $wished = $this->negotiator->getBest(
            $request->headers->get('Accept'),
            $this->formats->getMediaTypes()
        );

        if ($wished === null) {
            return false;
        }

        $format = $this->formats->getName($wished->getValue());

        return $this->serializer->supportsEncoding($format);
    }

    /**
     * Return the requested format
     *
     * @return string
     */
    public function getRequestedFormat(HttpRequest $request)
    {
        $accept = $this->negotiator->getBest(
            $request->headers->get('Accept'),
            $this->formats->getMediaTypes()
        );

        return $this->formats->getName($accept->getValue());
    }

    /**
     * Return the data payload as an object
     *
     * @param HttpRequest $request
     * @param ResourceDefinition $definition
     *
     * @return object|array
     */
    public function getData(
        HttpRequest $request,
        ResourceDefinition $definition
    ) {
        $format = $this->formats->getName(
            $request->headers->get('Content-Type')
        );

        return $this->serializer->deserialize(
            $request,
            HttpResourceInterface::class,
            $format,
            ['definition' => $definition]
        );
    }
}
