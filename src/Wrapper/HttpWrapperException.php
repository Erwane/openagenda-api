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
namespace OpenAgenda\Wrapper;

use OpenAgenda\OpenAgendaException;
use Psr\Http\Message\RequestInterface;

/**
 * HttpWrapper exception
 */
class HttpWrapperException extends OpenAgendaException
{
    /**
     * @var \Psr\Http\Message\RequestInterface
     */
    protected $_request;

    /**
     * Set Wrapper request in this exception.
     *
     * @param \Psr\Http\Message\RequestInterface $request Wrapper request.
     * @return void
     */
    public function setRequest(RequestInterface $request)
    {
        $this->_request = $request;
    }

    /**
     * Get exception response.
     *
     * @return \Psr\Http\Message\RequestInterface|null
     */
    public function getRequest(): ?RequestInterface
    {
        return $this->_request;
    }
}
