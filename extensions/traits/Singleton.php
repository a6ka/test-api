<?php

namespace app\extensions\traits;

trait Singleton
{

    private static $instance = null;

    /**
     * @return static
     */
    public static function getInstance()
    {
        if (null === self::$instance) {
            self::$instance = self::_create();
            if (method_exists(self::$instance, 'initInstance')) {
                self::$instance->initInstance();
            }
        }
        return self::$instance;
    }

    protected static function _create()
    {
        return new static();
    }
}
