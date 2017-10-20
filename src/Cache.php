<?php
namespace OpenAgenda;

use Symfony\Component\Cache\Simple\FilesystemCache;

class Cache
{
    /**
     * read cache value
     * @param  string $key cache key
     * @return mixed
     */
    public static function read($key)
    {
        $cache = new FilesystemCache();

        return $cache->get($key);
    }

    /**
     * write cache value
     * @param  string $key  cache key
     * @param  mixed $value cache value
     * @param  int $ttl     ttl cache lifetime
     * @return
     */
    public static function write($key, $value, $ttl = null)
    {
        $cache = new FilesystemCache();

        return $cache->set($key, $value, $ttl);
    }
}
