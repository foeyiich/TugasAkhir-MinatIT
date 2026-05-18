<?php

namespace TugasAkhir;

use BadFunctionCallException;

abstract class SingletonClass
{
    private static array $instances = [];

    protected function __construct()
    {
    }

    protected function __clone()
    {
    }

    public function __wakeup()
    {
        throw new BadFunctionCallException("Cannot unserialize a singleton.");
    }

    public static function getInstance(): static
    {
        $class = static::class;

        if (!isset(self::$instances[$class])) {
            self::$instances[$class] = new static();
        }

        return self::$instances[$class];
    }
}