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

use DateTimeInterface;
use Exception;

/**
 * DateTime class
 *
 * @noinspection PhpUnhandledExceptionInspection
 */
class DateTime extends \DateTimeImmutable
{
    /**
     * Create DateTime object from string or DateTimeInterface.
     *
     * @param \DateTimeInterface|string $datetime DateTime
     * @return \OpenAgenda\DateTime|static|null
     * @noinspection PhpDocMissingThrowsInspection
     */
    public static function parse($datetime)
    {
        $value = null;
        if (is_string($datetime)) {
            try {
                $value = new static($datetime);
            } catch (Exception $e) {
            }
        } elseif ($datetime instanceof static) {
            $value = $datetime;
        } elseif ($datetime instanceof DateTimeInterface) {
            /** @noinspection PhpUnhandledExceptionInspection */
            $value = new static($datetime->format(DateTimeInterface::ATOM));
        }

        return $value;
    }

    /**
     * DateTime as atom string.
     *
     * @return string
     */
    public function toAtomString()
    {
        return $this->format(DateTimeInterface::ATOM);
    }
}
