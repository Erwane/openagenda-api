<?php
declare(strict_types=1);

/**
 * OpenAgenda API client.
 * Copyright (c) Erwane BRETON
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright   Copyright (c) Erwane BRETON
 * @see         https://github.com/Erwane/openagenda-api
 * @license     https://opensource.org/licenses/mit-license.php MIT License
 */
namespace OpenAgenda\Endpoint;

use DateTimeInterface;
use DateTimeZone;
use OpenAgenda\DateTime;

/**
 * Abstract Endpoint
 *
 * @method bool exists() Check entity exists
 * @method mixed get() Get collection or entity
 * @method mixed create() Create entity
 * @method mixed update() Update entity (full data or partial)
 * @method mixed delete() Delete entity
 */
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
    protected $params = [];

    /**
     * Endpoint fields configuration.
     *
     * @var array
     */
    protected $_schema = [];

    /**
     * Construct OpenAgenda endpoint.
     *
     * @param array $params Endpoint params.
     */
    public function __construct(array $params = [])
    {
        $this->set($params);
    }

    /**
     * Set endpoint params.
     *
     * @param array<string, mixed> $params Params to set or param name.
     * @return void
     */
    public function set(array $params): void
    {
        foreach ($params as $param => $value) {
            $value = $this->_formatType($param, $value);

            $this->params[$param] = $value;
        }
    }

    /**
     * Format param value.
     *
     * @param string $param Param name
     * @param mixed $value Param value
     * @return mixed
     */
    protected function _formatType(string $param, $value)
    {
        if (!empty($this->_schema[$param]['type'])) {
            switch ($this->_schema[$param]['type']) {
                case 'datetime':
                        $value = DateTime::parse($value);
                    break;
                case 'array':
                    $value = $this->paramAsArray($value);
                    break;
            }
        }

        return $value;
    }

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
     * @param mixed $value Param value.
     * @return mixed
     */
    protected function convertQueryValue($value)
    {
        if ($value instanceof DateTimeInterface) {
            $value = $value->setTimezone(new DateTimeZone('UTC'))
                ->format('Y-m-d\TH:i:s');
        }

        return $value;
    }

    /**
     * Get OpenAgenda endpoint uri.
     *
     * @param string $method Request method
     * @return string
     * @throws \OpenAgenda\OpenAgendaException
     */
    public function getUrl(string $method): string
    {
        $method = strtolower($method);

        $path = $this->uriPath($method);
        $query = $this->uriQuery();

        $components = parse_url($this->baseUrl . $path);
        $url = sprintf('%s://%s%s', $components['scheme'], $components['host'], $components['path']);
        if ($query) {
            $url .= '?' . http_build_query($query);
        }

        return $url;
    }

    /**
     * Validate uri path params and return an empty path.
     * Endpoint SHOULD have an uriPath method and return endpoint path.
     *
     * @param string $method Request method (HEAD, GET, POST, PATCH, DELETE)
     * @return string
     * @throws \OpenAgenda\OpenAgendaException
     */
    abstract protected function uriPath(string $method): string;

    /**
     * Convert endpoint params to valid OpenAgenda endpoint query params.
     *
     * @return array
     * @throws \OpenAgenda\OpenAgendaException
     */
    protected function uriQuery(): array
    {
        $params = $this->params;
        $query = [];

        // Default to null
        $params += array_fill_keys(array_keys($this->_schema), null);

        // Keep-only valid fields
        $params = array_intersect_key($params, $this->_schema);

        foreach ($params as $param => $value) {
            $query[$param] = $this->convertQueryValue($value);
        }

        // filter
        return array_filter($query, function ($value) {
            return $value !== null;
        });
    }

    /**
     * Return endpoint params
     *
     * @return array
     * @throws \OpenAgenda\OpenAgendaException
     */
    public function toArray()
    {
        return [
            'exists' => $this->getUrl('exists'),
            'get' => $this->getUrl('get'),
            'create' => $this->getUrl('create'),
            'update' => $this->getUrl('update'),
            'delete' => $this->getUrl('delete'),
            'params' => $this->params,
        ];
    }
}
