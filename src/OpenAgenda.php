<?php
declare(strict_types=1);

namespace OpenAgenda;

use Cake\Validation\Validation as CakeValidation;
use OpenAgenda\Endpoint\Agenda;
use OpenAgenda\Endpoint\Endpoint;
use OpenAgenda\Endpoint\EndpointFactory;
use OpenAgenda\Endpoint\Event;
use OpenAgenda\Endpoint\Location;
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

    protected static $defaultLang = 'fr';

    /**
     * Project base url.
     *
     * @var string|null
     */
    protected static $projectBaseUrl = null;

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
            'projectUrl' => null,
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

        if ($params['projectUrl'] && !CakeValidation::url($params['projectUrl'])) {
            throw new OpenAgendaException('Invalid project url.');
        }

        self::$client = new Client($params);
        self::$defaultLang = $params['defaultLang'];
        self::$projectBaseUrl = $params['projectUrl'];
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
     * Get project url.
     *
     * @return string|null
     */
    public static function getProjectUrl(): ?string
    {
        return self::$projectBaseUrl;
    }

    /**
     * Set project url.
     *
     * @param string|null $projectUrl Project url. Used for `a` tags in html description.
     * @return void
     */
    public static function setProjectUrl(?string $projectUrl): void
    {
        self::$projectBaseUrl = $projectUrl;
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
     */
    public function agenda(array $params): Agenda
    {
        return EndpointFactory::make('/agenda', $params);
    }

    /**
     * Get OpenAgenda locations for an agenda.
     *
     * @param array $params Query params.
     * @return \OpenAgenda\Entity\Location[]|\Ramsey\Collection\Collection
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
     * @return \OpenAgenda\Endpoint\Location|\OpenAgenda\Endpoint\Endpoint
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
     * @return \OpenAgenda\Entity\Event[]|\Ramsey\Collection\Collection
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
     * @return \OpenAgenda\Endpoint\Event|\OpenAgenda\Endpoint\Endpoint
     * @throws \OpenAgenda\Endpoint\UnknownEndpointException
     */
    public function event(array $params = []): Event
    {
        return EndpointFactory::make('/event', $params);
    }
}
