<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class GlobalSetting extends Model
{
    protected $fillable = ['key', 'value', 'description'];

    /**
     * Obtiene un valor de configuración global por su clave.
     * Implementa caché para optimizar el rendimiento.
     */
    public static function get(string $key, $default = null)
    {
        return Cache::rememberForever("global_setting_{$key}", function () use ($key, $default) {
            $setting = self::where('key', $key)->first();
            return $setting ? $setting->value : $default;
        });
    }

    /**
     * Actualiza un valor de configuración y limpia la caché.
     */
    public static function set(string $key, $value): void
    {
        self::updateOrCreate(['key' => $key], ['value' => (string) $value]);
        Cache::forget("global_setting_{$key}");
    }
}
