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

use Cake\Validation\Validator;
use Cake\Validation\ValidatorAwareInterface;
use Cake\Validation\ValidatorAwareTrait;
use DateTime;
use InvalidArgumentException;
use League\Uri\Uri;

/**
 * Abstract Endpoint
 *
 * @method mixed exists() Check entity exists
 * @method mixed get() Get collection or entity
 * @method mixed create() Create entity
 * @method mixed update(bool $full) Update entity (full data or partial)
 * @method mixed delete() Delete entity
 */
abstract class Endpoint implements ValidatorAwareInterface
{
    use ValidatorAwareTrait;

    /**
     * The alias this object is assigned to validators as.
     *
     * @var string
     */
    public const VALIDATOR_PROVIDER_NAME = 'endpoint';

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
    protected $queryFields = [];

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
     * @param string[]|string $params Params to set or param name.
     * @param mixed $value Param value or null if params is an array.
     * @return void
     * @throws \DateMalformedStringException
     */
    public function set($params, $value = null): void
    {
        if (!is_array($params)) {
            $params = [$params => $value];
        }

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
     * @throws \DateMalformedStringException
     */
    protected function _formatType(string $param, $value)
    {
        if (!empty($this->queryFields[$param]['type'])) {
            switch ($this->queryFields[$param]['type']) {
                case 'datetime':
                    $value = new DateTime($value);
                    break;
                case 'array':
                    $value = $this->paramAsArray($value);
                    break;
            }
        }

        return $value;
    }

    /**
     * Validate URI path params. ex /agendas/<agenda_id>/locations/<location_id>
     *
     * @param \Cake\Validation\Validator $validator Validator
     * @return \Cake\Validation\Validator
     */
    public function validationUriPath(Validator $validator): Validator
    {
        return $validator;
    }

    /**
     * Validate endpoint parameters.
     *
     * @param array $params Endpoint parameters.
     * @return array
     */
    protected function validateParams(array $params): array
    {
        // Default to null
        $params += array_fill_keys(array_keys($this->queryFields), null);

        // Keep-only valid fields
        $params = array_intersect_key($params, $this->queryFields);

        // Validate
        $errors = $this->getValidator('default')
            ->validate($params);

        if ($errors) {
            $message = '';
            throw new InvalidArgumentException($message);
        }

        return $params;
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
     * @param array $map Param mapping.
     * @param mixed $value Param value.
     * @return mixed
     */
    protected function convertQueryValue(array $map, $value)
    {
        if ($value instanceof DateTime) {
            $value = $value->format('Y-m-d\TH:i:s');
        }

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
     * Get OpenAgenda endpoint uri.
     *
     * @param string $method Request method
     * @return \League\Uri\Uri
     */
    public function getUri(string $method): Uri
    {
        $method = strtolower($method);

        $path = $this->uriPath($method);
        $query = $this->uriQuery();

        $components = parse_url($this->baseUrl . $path);
        if ($query) {
            $components['query'] = http_build_query($query);
        }

        return Uri::createFromComponents($components);
    }

    /**
     * Validate uri path params and return an empty path.
     * Endpoint SHOULD have an uriPath method and return endpoint path.
     *
     * @param string $method Request method (HEAD, GET, POST, PATCH, DELETE)
     * @return string
     */
    public function uriPath(string $method): string
    {
        // validate Uri path params
        $validator = 'uriPath' . ucfirst(strtolower($method));
        if (method_exists($this, 'validation' . ucfirst($validator))) {
            $validator = $this->getValidator($validator);
        } else {
            $validator = $this->getValidator('uriPath');
        }

        $errors = $validator->validate($this->params);

        if ($errors) {
            $this->throwException($errors);
        }

        // Return no path. Endpoint method should set this
        return '';
    }

    /**
     * Convert endpoint params to valid OpenAgenda endpoint query params.
     *
     * @return array
     */
    protected function uriQuery(): array
    {
        $query = [];

        $params = $this->validateParams($this->params);

        foreach ($params as $param => $value) {
            if (!isset($this->queryFields[$param])) {
                continue;
            }

            $map = $this->queryFields[$param];
            $query[$map['name']] = $this->convertQueryValue($map, $value);
        }

        // filter
        return array_filter($query, function ($value) {
            return $value !== null;
        });
    }

    /**
     * Throw exception with endpoint errors
     *
     * @param array $errors Endpoint errors
     * @return void
     * @throws \InvalidArgumentException
     */
    protected function throwException(array $errors)
    {
        $message = [
            'message' => static::class . ' has errors.',
            'errors' => $errors,
        ];

        throw new InvalidArgumentException(json_encode($message, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }
}
