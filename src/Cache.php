<?php
namespace OpenAgenda;

use Symfony\Component\Cache\Simple\FilesystemCache;

class Cache
{
    public static function read($key)
    {
        $cache = new FilesystemCache();

        return $cache->get($key);
    }
}
