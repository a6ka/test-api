<?php

namespace app\extensions\traits;

use app\extensions\ModelCache;

trait Cache
{

    /**
     *
     * @param int|null $lifetime
     * @param bool $resetCache
     * @param bool $resetCacheOnly
     *
     * @return self
     */
    public function cache($lifetime = 30, $resetCache = false, $resetCacheOnly = false)
    {
        return new ModelCache($this, $lifetime, $resetCache, $resetCacheOnly);
    }
}
