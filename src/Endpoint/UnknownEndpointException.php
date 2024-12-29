<?php
declare(strict_types=1);

namespace OpenAgenda\Endpoint;

use OpenAgenda\OpenAgendaException;
use Throwable;

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
