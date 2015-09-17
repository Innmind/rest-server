<?php

namespace Innmind\Rest\Server;

class Formats
{
    protected $formats = [];

    /**
     * Add a new supported format
     *
     * @param string $name Format name (ie: json or html)
     * @param string $mediaType Like "application/json" for json
     * @param int $priority
     *
     * @return Formats self
     */
    public function add($name, $mediaType, $priority)
    {
        $this->formats[] = [
            'name' => (string) $name,
            'mediaType' => (string) $mediaType,
            'priority' => (int) $priority,
        ];

        return $this;
    }

    /**
     * Check if the given value is a supported format name or media type
     *
     * @param string $value
     *
     * @return bool
     */
    public function has($value)
    {
        $value = (string) $value;

        foreach ($this->formats as $format) {
            if ($format['name'] === $value || $format['mediaType'] === $value) {
                return true;
            }
        }

        return false;
    }

    /**
     * Return the name for the given media type
     *
     * @param string $mediaType
     *
     * @throws InvalidArgumentException If the type is unknown
     *
     * @return string
     */
    public function getName($mediaType)
    {
        $mediaType = (string) $mediaType;

        foreach ($this->formats as $format) {
            if ($format['mediaType'] === $mediaType) {
                return $format['name'];
            }
        }

        throw new \InvalidArgumentException(sprintf(
            'Unknown media type "%s"',
            $mediaType
        ));
    }

    /**
     * Return all the media types ordered by priority
     *
     * @return array
     */
    public function getMediaTypes()
    {
        $types = [];

        foreach ($this->formats as $format) {
            $types[$format['mediaType']] = $format['priority'];
        }

        arsort($types);

        return array_keys($types);
    }

    /**
     * Return the prefered media type for the given format
     *
     * @param string $format
     *
     * @return string
     */
    public function getMediaType($format)
    {
        $priority = null;
        $type = null;

        foreach ($this->formats as $knownFormat) {
            if ($knownFormat['name'] !== (string) $format) {
                continue;
            }

            if ($type === null) {
                $priority = $knownFormat['priority'];
                $type = $knownFormat['mediaType'];

                continue;
            }

            if ($knownFormat['priority'] > $priority) {
                $priority = $knownFormat['priority'];
                $type = $knownFormat['mediaType'];
            }
        }

        if ($type === null) {
            throw new \LogicException(sprintf(
                'No media type found for the format "%s"',
                $format
            ));
        }

        return $type;
    }
}
