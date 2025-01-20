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

use Exception;
use Psr\Http\Message\ResponseInterface;

/**
 * OpenAgenda exception
 */
class OpenAgendaException extends Exception
{
    /**
     * @var \Psr\Http\Message\ResponseInterface|null
     */
    protected ?ResponseInterface $_response = null;

    /**
     * @var array
     */
    protected array $_payload = [];

    /**
     * Set Wrapper response in this exception.
     *
     * @param \Psr\Http\Message\ResponseInterface $response Wrapper response.
     * @return void
     */
    public function setResponse(ResponseInterface $response)
    {
        $this->_response = $response;
    }

    /**
     * Get exception response.
     *
     * @return \Psr\Http\Message\ResponseInterface|null
     */
    public function getResponse(): ?ResponseInterface
    {
        return $this->_response;
    }

    /**
     * Set response payload.
     *
     * @param array $payload Response payload.
     * @return void
     */
    public function setPayload(array $payload = [])
    {
        $this->_payload = $payload;
    }

    /**
     * Get exception payload.
     *
     * @return array
     */
    public function getPayload(): array
    {
        return $this->_payload;
    }
}
