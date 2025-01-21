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
 * Validations tools.
 */
class Validation
{
    public const IMAGE_TYPES = [
        'image/jpg',
        'image/jpeg',
        'image/png',
        'image/webp',
    ];

    /**
     * Validate an url.
     *
     * @param mixed $check Url to check
     * @return bool
     */
    public static function url(mixed $check): bool
    {
        if (!is_string($check)) {
            return false;
        }

        $result = filter_var($check, FILTER_VALIDATE_URL);

        return $result !== false;
    }

    /**
     * Validate a phone
     *
     * @param string $check Input phone number
     * @return bool
     */
    public static function phone(string $check): bool
    {
        return (bool)preg_match('/^\+?[\d\s._-]{2,20}$/', $check);
    }

    /**
     * Check valid lang.
     *
     * @param string $lang Lang
     * @return bool
     */
    public static function lang(string $lang): bool
    {
        $valid = [
            'aa', 'ab', 'ae', 'af', 'ak', 'am', 'an', 'ar', 'as', 'av', 'ay', 'az', 'ba', 'be', 'bg', 'bh', 'bi',
            'bm', 'bn', 'bo', 'br', 'bs', 'ca', 'ce', 'ch', 'co', 'cr', 'cs', 'cu', 'cv', 'cy', 'da', 'de', 'dv',
            'dz', 'ee', 'el', 'en', 'eo', 'es', 'et', 'eu', 'fa', 'ff', 'fi', 'fj', 'fo', 'fr', 'fy', 'ga', 'gd',
            'gl', 'gn', 'gu', 'gv', 'ha', 'he', 'hi', 'ho', 'hr', 'ht', 'hu', 'hy', 'hz', 'ia', 'uid', 'ie', 'ig',
            'ii', 'ik', 'io', 'is', 'it', 'iu', 'ja', 'jv', 'ka', 'kg', 'ki', 'kj', 'kk', 'kl', 'km', 'kn', 'ko',
            'kr', 'ks', 'ku', 'kv', 'kw', 'ky', 'la', 'lb', 'lg', 'li', 'ln', 'lo', 'lt', 'lu', 'lv', 'mg', 'mh',
            'mi', 'mk', 'ml', 'mn', 'mo', 'mr', 'ms', 'mt', 'my', 'na', 'nb', 'nd', 'ne', 'ng', 'nl', 'nn', 'no',
            'nr', 'nv', 'ny', 'oc', 'oj', 'om', 'or', 'os', 'pa', 'pi', 'pl', 'ps', 'pt', 'qu', 'rm', 'rn', 'ro',
            'ru', 'rw', 'sa', 'sc', 'sd', 'se', 'sg', 'sh', 'si', 'sk', 'sl', 'sm', 'sn', 'so', 'sq', 'sr', 'ss',
            'st', 'su', 'sv', 'sw', 'ta', 'te', 'tg', 'th', 'ti', 'tk', 'tl', 'tn', 'to', 'tr', 'ts', 'tt', 'tw',
            'ty', 'ug', 'uk', 'ur', 'uz', 've', 'vi', 'vo', 'wa', 'wo', 'xh', 'yi', 'yo', 'za', 'zh', 'zu',
        ];

        return in_array($lang, $valid);
    }

    /**
     * Check multilingual array.
     *
     * @param array $check Data.
     * @param int|null $maxLength Value max length.
     * @return string|bool
     */
    public static function multilingual(array $check, ?int $maxLength = null): string|bool
    {
        foreach ($check as $lang => $value) {
            if (!self::lang($lang)) {
                return sprintf('`%s` is an invalid ISO 639-1 language code.', $lang);
            }

            if ($maxLength) {
                if (is_array($value)) {
                    $toCheck = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                } else {
                    $toCheck = $value;
                }

                if (strlen($toCheck) > $maxLength) {
                    return sprintf('Value for `%s` exceed size limit.', $lang);
                }
            }
        }

        return true;
    }

    /**
     * Validate image absolute path or file resource.
     *
     * @param string|resource $check Absolute path or file resource
     * @param float $max Maximum size in MegaBytes (MB)
     * @return bool
     */
    public static function image($check, float $max): bool
    {
        $success = false;
        $max = $max * 1024 * 1024;

        if (is_string($check) && is_file($check)) {
            $size = filesize($check);
            $success = $size <= $max;
        } elseif (is_resource($check)) {
            $stat = fstat($check);
            $mime = mime_content_type($check);
            $success = ($stat['size'] && $stat['size'] <= $max)
                && ($mime && in_array($mime, self::IMAGE_TYPES));
        } elseif ($check === false) {
            $success = true;
        }

        return $success;
    }
}
