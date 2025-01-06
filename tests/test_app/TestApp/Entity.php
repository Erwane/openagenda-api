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
namespace OpenAgenda\Test\test_app\TestApp;

/**
 * Entity for tests
 */
class Entity extends \OpenAgenda\Entity\Entity
{
    protected $_aliases = [
        'id' => ['field' => 'uid'],
        'postal_code' => ['field' => 'postalCode'],
        'created_at' => ['field' => 'createdAt', 'type' => 'DateTime'],
        'description' => ['field' => 'description', 'type' => 'json'],
    ];
}
