<?php
declare(strict_types=1);

namespace OpenAgenda;

use OpenAgenda\Endpoint\Endpoint;
use OpenAgenda\Endpoint\EndpointFactory;
use OpenAgenda\Wrapper\HttpWrapper;
use Psr\SimpleCache\CacheInterface;
use Ramsey\Collection\Collection;

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
    protected static $client = null;

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

        self::$client = new Client($params);
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
     * Do a GET request on $path.
     *
     * @param string $path Endpoint path. Relative, not real OpenAgenda endpoint.
     * @param array $params Client options
     * @return \Ramsey\Collection\Collection|\OpenAgenda\Entity\Entity|\Psr\Http\Message\ResponseInterface
     * @throws \OpenAgenda\Endpoint\UnknownEndpointException
     */
    public function get(string $path, array $params = [])
    {
        // todo: allow passing raw OpenAgenda endpoint url and return ResponseInterface.
        // todo: return response or json payload
        return EndpointFactory::make($path, $params)->get();
    }

    /**
     * Do a POST request on $path.
     *
     * @param string $path Endpoint path. Relative, not real OpenAgenda endpoint.
     * @param array $params Client options
     * @return \Ramsey\Collection\Collection|\OpenAgenda\Entity\Entity|\Psr\Http\Message\ResponseInterface
     * @throws \OpenAgenda\Endpoint\UnknownEndpointException
     */
    public function post(string $path, array $params = [])
    {
        // todo: allow passing raw OpenAgenda endpoint url and return ResponseInterface.
        // todo: return response or json payload
        // todo: post request replaced by ::create()
        return EndpointFactory::make($path, $params)->post();
    }

    /**
     * Do a PATCH request on $path.
     *
     * @param string $path Endpoint path. Relative, not real OpenAgenda endpoint.
     * @param array $params Client options
     * @return \Ramsey\Collection\Collection|\OpenAgenda\Entity\Entity|\Psr\Http\Message\ResponseInterface
     * @throws \OpenAgenda\Endpoint\UnknownEndpointException
     */
    public function patch(string $path, array $params = [])
    {
        // todo: allow passing raw OpenAgenda endpoint url and return ResponseInterface.
        // todo: return response or json payload
        return EndpointFactory::make($path, $params)->patch();
    }

    /**
     * Do a DELETE request on $path.
     *
     * @param string $path Endpoint path. Relative, not real OpenAgenda endpoint.
     * @param array $params Client options
     * @return \Ramsey\Collection\Collection|\OpenAgenda\Entity\Entity|\Psr\Http\Message\ResponseInterface
     * @throws \OpenAgenda\Endpoint\UnknownEndpointException
     */
    public function delete(string $path, array $params = [])
    {
        // todo: allow passing raw OpenAgenda endpoint url and return ResponseInterface.
        // todo: return response or json payload
        return EndpointFactory::make($path, $params)->delete();
    }

    /**
     * Get agendas from OpenAgenda.
     *
     * @param array $params Query params.
     * @return \OpenAgenda\Entity\Agenda[]|\Ramsey\Collection\Collection
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
     * @return \OpenAgenda\Entity\Agenda[]|\Ramsey\Collection\Collection
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
     * @return \OpenAgenda\Endpoint\Agenda|\OpenAgenda\Endpoint\Endpoint
     * @throws \OpenAgenda\Endpoint\UnknownEndpointException
     * @uses \OpenAgenda\Endpoint\Agendas
     */
    public function agenda(array $params): Endpoint
    {
        return EndpointFactory::make('/agenda', $params);
    }

    /**
     * Get OpenAgenda locations for an agenda.
     *
     * @param array $params Query params.
     * @return \OpenAgenda\Entity\Location[]|\Ramsey\Collection\Collection
     * @throws \OpenAgenda\Endpoint\UnknownEndpointException
     */
    public function locations(array $params = []): Collection
    {
        return EndpointFactory::make('/locations', $params)->get();
    }

    /**
     * Get OpenAgenda location endpoint.
     *
     * @param array $params Endpoint params.
     * @return \OpenAgenda\Endpoint\Location|\OpenAgenda\Endpoint\Endpoint
     * @throws \OpenAgenda\Endpoint\UnknownEndpointException
     */
    public function location(array $params = []): Endpoint
    {
        return EndpointFactory::make('/location', $params);
    }
}
