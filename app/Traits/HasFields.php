<?php

namespace App\Traits;

trait HasFields
{
    /**
     * Get the mapped field name.
     *
     * @param string $key
     * @return string
     */
    public static function field(string $key): string
    {
        return static::$fields[$key] ?? $key;
    }

    /**
     * Get all fields mapping (optional helper).
     *
     * @return array
     */
    public static function fields(): array
    {
        return static::$fields ?? [];
    }
}
