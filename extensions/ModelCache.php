<?php

namespace app\extensions;

use Exception;
use Yii;

class ModelCache
{
    private $object;
    private $cache;
    private $lifetime;
    private $resetCache;
    private $resetCacheOnly;

    public function __construct($object, $lifetime = 30, $resetCache = false, $resetCacheOnly = false)
    {
        $this->object         = $object;
        $this->cache          = Yii::$app->cache;
        $this->lifetime       = $lifetime;
        $this->resetCache     = $resetCache;
        $this->resetCacheOnly = $resetCacheOnly;
    }

    public function __call($method, $args)
    {
        $classMethods = get_class_methods($this->object);

        if (in_array($method, $classMethods)) {
            if (false === ($data = $this->get($method, $args))) {
                if ($this->resetCache && $this->resetCacheOnly) {
                    return null;
                }
                $data = call_user_func_array(array($this->object, $method), $args);
                $this->set($method, $args, $data);
            }
            return $data;
        }

        throw new Exception('Method ' . $method . ' does not exist in class ' . get_class($this->object));
    }

    /**
     * Get data from memcached
     *
     * @param string $method
     * @param array $args
     *
     * @return array|bool cached data
     */
    private function get($method, $args)
    {
        $cacheKey = $this->generateCacheKey($method, $args);
        if ($this->resetCache) {
            $this->cache->delete($cacheKey);
            return false;
        }
        return $this->cache->get($cacheKey);
    }

    /**
     * set data to memcached
     *
     * @param string $method
     * @param array $args
     * @param array $data
     *
     * @return bool
     */
    private function set($method, $args, $data)
    {
        if (null === $data) {
            return false;
        }
        $cacheKey = $this->generateCacheKey($method, $args);
        return $this->cache->set($cacheKey, $data, $this->lifetime);
    }

    private function generateCacheKey(string $method, $args): string
    {
        return md5(get_class($this->object) . $method . print_r($args, true));
    }
}