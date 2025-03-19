<?php

namespace App\Traits;

trait HasFields
{
    public static function getField(string $key)
    {
        return static::$fields[$key] ?? $key;
    }
    public static function __callStatic($name, $arguments)
    {
        return static::getField($name);
    }
}
