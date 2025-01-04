<?php
declare(strict_types=1);

namespace OpenAgenda\Endpoint;

use Cake\Validation\ValidatorAwareInterface;
use Cake\Validation\ValidatorAwareTrait;
use DateTime;
use InvalidArgumentException;
use League\Uri\Uri;

/**
 * Abstract Endpoint
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
    protected $params;

    /**
     * Endpoint fields configuration.
     *
     * @var array
     */
    protected $fields = [];

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
        if (!empty($this->fields[$param]['type'])) {
            switch ($this->fields[$param]['type']) {
                case 'datetime':
                    $value = new DateTime($value);
            }
        }

        return $value;
    }

    /**
     * Validate endpoint parameters.
     *
     * @param array $params Endpoint parameters.
     * @return array
     */
    protected function validateParams(array $params): array
    {
        $params += array_fill_keys(array_keys($this->fields), null);

        $validator = $this->getValidator('default');

        $errors = $validator->validate($params);

        if ($errors) {
            dump($errors);
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
     * Convert endpoint params to valid OpenAgenda endpoint query params.
     *
     * @return array
     */
    protected function uriQuery(): array
    {
        $query = [];

        $params = $this->validateParams($this->params);

        foreach ($params as $param => $value) {
            if (!isset($this->fields[$param])) {
                continue;
            }

            $map = $this->fields[$param];
            $query[$map['name']] = $this->convertQueryValue($map, $value);
        }

        // filter
        return array_filter($query, function ($value) {
            return $value !== null;
        });
    }

    /**
     * Get OpenAgenda endpoint uri.
     *
     * @return \League\Uri\Uri
     */
    abstract public function getUri(): Uri;
}
