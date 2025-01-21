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

/**
 * OpenAgenda Validator
 */
class Validator extends \Cake\Validation\Validator
{
    /**
     * @inheritDoc
     */
    public function __construct()
    {
        parent::__construct();

        // Don't use I18n. This can create a `Cache not initialized` problem
        $this->_useI18n = false;
    }

    /**
     * {@inheritDoc}
     * Override isArray to avoid deprecation warnings.
     *
     * @codeCoverageIgnore
     */
    public function isArray($field, $message = null, $when = null)
    {
        if (method_exists($this, 'array')) {
            $extra = array_filter(['on' => $when, 'message' => $message]);

            return $this->add($field, 'isArray', $extra + [
                    'rule' => 'isArray',
                ]);
        } else {
            return parent::isArray($field, $message, $when);
        }
    }
}
