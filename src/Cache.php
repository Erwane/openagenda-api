<?php
declare(strict_types=1);

namespace OpenAgenda;

use Psr\SimpleCache\InvalidArgumentException;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Psr16Cache;

class Cache
{
    protected static $_cache;

    protected static function cache(): Psr16Cache
    {
        if (!static::$_cache) {
            static::$_cache = new Psr16Cache(new FilesystemAdapter());
        }

        return static::$_cache;
    }

    /**
     * Read cache value
     *
     * @param string $key cache key
     * @return mixed|null
     * @deprecated 2.1.0
     * @codeCoverageIgnore
     */
    public static function read(string $key)
    {
        return static::get($key);
    }

    /**
     * write cache value
     *
     * @param string $key cache key
     * @param mixed $value cache value
     * @param int|\DateInterval|null $ttl
     * @return mixed
     * @deprecated 2.1.0
     * @codeCoverageIgnore
     */
    public static function write(string $key, $value, $ttl = null)
    {
        return static::set($key, $value, $ttl);
    }

    /**
     * Get cache value
     *
     * @param string $key Cache key name
     * @return mixed|null
     */
    public static function get(string $key)
    {
        $value = null;
        try {
            $value = static::cache()->get($key);
        } catch (InvalidArgumentException $e) {
        }

        return $value;
    }

    /**
     * Set cache value
     *
     * @param string $key Cache key name
     * @param mixed $value Value
     * @param int|\DateInterval|null $ttl
     * @return bool
     */
    public static function set(string $key, $value, $ttl = null): bool
    {
        try {
            return static::cache()->set($key, $value, $ttl);
        } catch (InvalidArgumentException $e) {
            return false;
        }
    }

    public static function clear(): bool
    {
        return static::cache()->clear();
    }
}
