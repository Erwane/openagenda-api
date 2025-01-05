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

use OpenAgenda\OpenAgendaException;
use Throwable;

/**
 * UnknownEndpointException
 */
class UnknownEndpointException extends OpenAgendaException
{
    /**
     * @inheritDoc
     */
    public function __construct($message = '', $code = 0, ?Throwable $previous = null)
    {
        $path = $message;

        $message = sprintf(
            'Path "%s" is not a valid endpoint.',
            $path
        );

        parent::__construct($message, $code, $previous);
    }
}
