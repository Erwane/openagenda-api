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
namespace OpenAgenda;

use OpenAgenda\Endpoint\Agenda;
use OpenAgenda\Endpoint\EndpointFactory;
use OpenAgenda\Endpoint\Event;
use OpenAgenda\Endpoint\Location;
use OpenAgenda\Endpoint\Raw;
use OpenAgenda\Wrapper\HttpWrapper;
use Psr\Http\Message\ResponseInterface;
use Psr\SimpleCache\CacheInterface;

/**
 * OpenAgenda
 */
class OpenAgenda
{
    /**
     * OpenAgenda client
     *
     * @var \OpenAgenda\Client|null
     */
    protected static ?Client $client = null;

    /**
     * @var string
     */
    protected static string $defaultLang = 'fr';

    /**
     * OpenAgenda.
     *
     * @param array $params OpenAgenda params.
     * @throws \OpenAgenda\OpenAgendaException
     */
    public function __construct(array $params = [])
    {
        $params += [
            'public_key' => null,
            'private_key' => null,
            'wrapper' => null,
            'cache' => null,
            'defaultLang' => 'fr',
        ];

        if (!$params['public_key']) {
            throw new OpenAgendaException('Missing `public_key`.');
        }

        if (!($params['wrapper'] instanceof HttpWrapper)) {
            throw new OpenAgendaException('Invalid or missing `wrapper`.');
        }

        if ($params['cache'] && !($params['cache'] instanceof CacheInterface)) {
            throw new OpenAgendaException('Cache should implement \Psr\SimpleCache\CacheInterface.');
        }

        if (!Validation::lang($params['defaultLang'])) {
            throw new OpenAgendaException('Invalid defaultLang.');
        }

        self::$client = new Client($params);
        self::$defaultLang = $params['defaultLang'];
    }

    /**
     * Set static client.
     *
     * @param \OpenAgenda\Client $client OpenAgenda client.
     * @return void
     */
    public static function setClient(Client $client)
    {
        self::$client = $client;
    }

    /**
     * Get OpenAgenda client.
     *
     * @return \OpenAgenda\Client|null
     */
    public static function getClient(): ?Client
    {
        return self::$client;
    }

    /**
     * Reset client
     *
     * @return void
     */
    public static function resetClient()
    {
        self::$client = null;
    }

    /**
     * OpenAgenda default lang
     *
     * @return string
     */
    public static function getDefaultLang(): string
    {
        return self::$defaultLang;
    }

    /**
     * Do a HEAD request on $path.
     *
     * @param string $path Endpoint path. Relative, not real OpenAgenda endpoint.
     * @param array $params Client options
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \OpenAgenda\OpenAgendaException
     */
    public function head(string $path, array $params = []): ResponseInterface
    {
        $endpoint = new Raw();
        $url = $endpoint->getUrl(__FUNCTION__) . $path;
        $params['_raw'] = true;

        return self::$client->head($url, $params);
    }

    /**
     * Do a GET request on $path.
     *
     * @param string $path Endpoint path. Relative, not real OpenAgenda endpoint.
     * @param array $params Client options
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \OpenAgenda\OpenAgendaException
     */
    public function get(string $path, array $params = []): ResponseInterface
    {
        $endpoint = new Raw();
        $url = $endpoint->getUrl(__FUNCTION__) . $path;
        $params['_raw'] = true;

        return self::$client->get($url, $params);
    }

    /**
     * Do a POST request on $path.
     *
     * @param string $path Endpoint path. Relative, not real OpenAgenda endpoint.
     * @param array $data Request data
     * @param array $params Client options
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \OpenAgenda\OpenAgendaException
     */
    public function post(string $path, array $data = [], array $params = [])
    {
        $endpoint = new Raw();
        $url = $endpoint->getUrl(__FUNCTION__) . $path;
        $params['_raw'] = true;

        return self::$client->post($url, $data, $params);
    }

    /**
     * Do a PATCH request on $path.
     *
     * @param string $path Endpoint path. Relative, not real OpenAgenda endpoint.
     * @param array $data Request data
     * @param array $params Client options
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \OpenAgenda\OpenAgendaException
     */
    public function patch(string $path, array $data = [], array $params = [])
    {
        $endpoint = new Raw();
        $url = $endpoint->getUrl(__FUNCTION__) . $path;
        $params['_raw'] = true;

        return self::$client->patch($url, $data, $params);
    }

    /**
     * Do a DELETE request on $path.
     *
     * @param string $path Endpoint path. Relative, not real OpenAgenda endpoint.
     * @param array $params Client options
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \OpenAgenda\OpenAgendaException
     */
    public function delete(string $path, array $params = [])
    {
        $endpoint = new Raw();
        $url = $endpoint->getUrl(__FUNCTION__) . $path;
        $params['_raw'] = true;

        return self::$client->delete($url, $params);
    }

    /**
     * Get agendas from OpenAgenda.
     *
     * @param array $params Query params.
     * @return \OpenAgenda\Collection<\OpenAgenda\Entity\Agenda>
     * @throws \OpenAgenda\Endpoint\UnknownEndpointException
     * @uses \OpenAgenda\Endpoint\Agendas::get()
     */
    public function agendas(array $params = []): Collection
    {
        return EndpointFactory::make('/agendas', $params)->get();
    }

    /**
     * Get agendas from OpenAgenda.
     *
     * @param array $params Query params.
     * @return \OpenAgenda\Collection<\OpenAgenda\Entity\Agenda>
     * @throws \OpenAgenda\Endpoint\UnknownEndpointException
     * @uses \OpenAgenda\Endpoint\Agendas
     */
    public function myAgendas(array $params = []): Collection
    {
        return EndpointFactory::make('/agendas/mines', $params)->get();
    }

    /**
     * Get one agenda from OpenAgenda.
     *
     * @param array $params Query params.
     * @return \OpenAgenda\Endpoint\Agenda
     * @throws \OpenAgenda\Endpoint\UnknownEndpointException
     */
    public function agenda(array $params): Agenda
    {
        return EndpointFactory::make('/agenda', $params);
    }

    /**
     * Get OpenAgenda locations for an agenda.
     *
     * @param array $params Query params.
     * @return \OpenAgenda\Collection<\OpenAgenda\Entity\Location>
     * @throws \OpenAgenda\Endpoint\UnknownEndpointException
     * @uses \OpenAgenda\Endpoint\Locations
     */
    public function locations(array $params = []): Collection
    {
        return EndpointFactory::make('/locations', $params)->get();
    }

    /**
     * Get OpenAgenda location endpoint.
     *
     * @param array $params Endpoint params.
     * @return \OpenAgenda\Endpoint\Location
     * @throws \OpenAgenda\Endpoint\UnknownEndpointException
     */
    public function location(array $params = []): Location
    {
        return EndpointFactory::make('/location', $params);
    }

    /**
     * Get OpenAgenda events for an agenda.
     *
     * @param array $params Query params.
     * @return \OpenAgenda\Collection<\OpenAgenda\Entity\Event>
     * @throws \OpenAgenda\Endpoint\UnknownEndpointException
     * @uses \OpenAgenda\Endpoint\Events
     */
    public function events(array $params = []): Collection
    {
        return EndpointFactory::make('/events', $params)->get();
    }

    /**
     * Get OpenAgenda event endpoint.
     *
     * @param array $params Endpoint params.
     * @return \OpenAgenda\Endpoint\Event
     * @throws \OpenAgenda\Endpoint\UnknownEndpointException
     */
    public function event(array $params = []): Event
    {
        return EndpointFactory::make('/event', $params);
    }
}
