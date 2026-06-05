<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppSetting extends Model
{
    protected $fillable = ['key', 'value'];

    /**
     * Retrieve a setting value by key, with an optional default.
     *
     * Results are cached for the lifetime of the request so the database
     * is only hit once per key, even if get() is called in multiple places.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        static $cache = [];

        if (! array_key_exists($key, $cache)) {
            $setting = static::where('key', $key)->first();
            $cache[$key] = $setting?->value ?? $default;
        }

        return $cache[$key];
    }

    /**
     * Update or insert a setting by key.
     */
    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
    }
}
