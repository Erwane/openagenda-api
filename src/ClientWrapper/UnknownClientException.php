<?php
declare(strict_types=1);

namespace OpenAgenda\ClientWrapper;

use OpenAgenda\OpenAgendaException;
use Throwable;

class UnknownClientException extends OpenAgendaException
{
    /**
     * @inheritDoc
     */
    public function __construct($message = '', $code = 0, ?Throwable $previous = null)
    {
        $httpClassName = $message;

        $message = sprintf(
            'Http client "%s" is not supported yet. Please open issue or PR with your client class name.',
            $httpClassName
        );

        parent::__construct($message, $code, $previous);
    }
}
