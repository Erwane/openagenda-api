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
use DateTimeInterface;
use DateTimeZone;
use League\Uri\Uri;
use OpenAgenda\DateTime;
use OpenAgenda\OpenAgendaException;

/**
 * Abstract Endpoint
 *
 * @method bool exists() Check entity exists
 * @method mixed get() Get collection or entity
 * @method mixed create(bool $validate) Create entity
 * @method mixed update(bool $validate) Update entity (full data or partial)
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
     * Validate URI path params. ex /agendas/<agendaUid>/locations/<locationUid>
     *
     * @param \Cake\Validation\Validator $validator Validator
     * @return \Cake\Validation\Validator
     */
    public function validationUriPath(Validator $validator): Validator
    {
        return $validator;
    }

    /**
     * Validate URI query. ex /agendas?size=<size>&state=<state>
     *
     * @param \Cake\Validation\Validator $validator Validator
     * @return \Cake\Validation\Validator
     */
    public function validationUriQuery(Validator $validator): Validator
    {
        return $validator;
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
     * @param bool $validate Validate path parameters if true
     * @return \League\Uri\Uri
     * @throws \OpenAgenda\OpenAgendaException
     */
    public function getUri(string $method, bool $validate = true): Uri
    {
        $method = strtolower($method);

        $path = $this->uriPath($method, $validate);
        $query = $this->uriQuery($method, $validate);

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
     * @param bool $validate Validate path parameters if true
     * @return string
     * @throws \OpenAgenda\OpenAgendaException
     */
    protected function uriPath(string $method, bool $validate = true): string
    {
        if ($validate) {
            $method = strtolower($method);
            // validate Uri path params
            $validator = 'uriPath' . ucfirst($method);
            if (method_exists($this, 'validation' . ucfirst($validator))) {
                $validator = $this->getValidator($validator);
            } else {
                $validator = $this->getValidator('uriPath');
            }

            $errors = $validator->validate($this->params, $method === 'create');

            if ($errors) {
                $this->throwException($errors);
            }
        }

        // Return no path. Endpoint method should set this
        return '';
    }

    /**
     * Convert endpoint params to valid OpenAgenda endpoint query params.
     *
     * @param string $method Validate path parameters if true
     * @param bool $validate Do query validation
     * @return array
     * @throws \OpenAgenda\OpenAgendaException
     */
    protected function uriQuery(string $method, bool $validate = true): array
    {
        $params = $this->params;
        $query = [];

        // Default to null
        $params += array_fill_keys(array_keys($this->_schema), null);

        // Keep-only valid fields
        $params = array_intersect_key($params, $this->_schema);

        if ($validate) {
            // validate Uri path params
            $validator = 'uriQuery' . ucfirst(strtolower($method));
            if (method_exists($this, 'validation' . ucfirst($validator))) {
                $validator = $this->getValidator($validator);
            } else {
                $validator = $this->getValidator('uriQuery');
            }

            $errors = $validator->validate($this->params);

            if ($errors) {
                $this->throwException($errors);
            }
        }

        foreach ($params as $param => $value) {
            $query[$param] = $this->convertQueryValue($value);
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
     * @throws \OpenAgenda\OpenAgendaException
     */
    protected function throwException(array $errors)
    {
        $message = [
            'message' => static::class . ' has errors.',
            'errors' => $errors,
        ];

        throw new OpenAgendaException(json_encode($message, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
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
            'exists' => $this->getUri('exists', false)->__toString(),
            'get' => $this->getUri('get', false)->__toString(),
            'create' => $this->getUri('create', false)->__toString(),
            'update' => $this->getUri('update', false)->__toString(),
            'delete' => $this->getUri('delete', false)->__toString(),
            'params' => $this->params,
        ];
    }
}
