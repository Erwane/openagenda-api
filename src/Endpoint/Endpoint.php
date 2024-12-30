<?php
declare(strict_types=1);

namespace OpenAgenda\Endpoint;

use League\Uri\Uri;

abstract class Endpoint
{
    /**
     * OpenAgenda Api base url.
     *
     * @var string
     */
    protected $baseUrl = 'https://api.openagenda.com/v2';

    /**
     * @var array
     */
    protected $params;

    /**
     * Construct OpenAgenda endpoint.
     *
     * @param array $params Endpoint params.
     */
    public function __construct(array $params = [])
    {
        $this->params = $params;
    }

    /**
     * Validate endpoint parameters.
     *
     * @param array $params Endpoint parameters.
     * @return array
     */
    abstract protected function validateParams(array $params): array;

    /**
     * Convert a string/numeric param as an array value.
     *
     * @param mixed $value Param value
     * @return mixed
     */
    protected function paramAsArray($value)
    {
        if (is_string($value) || is_numeric($value)) {
            return [$value];
        }

        return $value;
    }

    /**
     * Convert query value to match OpenAgenda query value.
     *
     * @param array $map Param mapping.
     * @param mixed $value Param value.
     * @return mixed
     */
    protected function convertQueryValue(array $map, $value)
    {
        if (isset($map['matching'])) {
            if (is_string($value) && isset($map['matching'][$value])) {
                $value = $map['matching'][$value];
            } elseif (is_array($value)) {
                dump($map['matching'], $value);
            }
        }

        return $value;
    }

    /**
     * Convert endpoint params to valid OpenAgenda endpoint query params.
     *
     * @return array
     */
    abstract protected function uriQuery(): array;

    /**
     * Get OpenAgenda endpoint uri.
     *
     * @return \League\Uri\Uri
     */
    abstract public function getUri(): Uri;
}
