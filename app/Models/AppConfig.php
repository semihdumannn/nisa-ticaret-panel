<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class AppConfig extends Model
{
    protected $table = 'app_configs';

    protected $primaryKey = 'key';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'key',
        'value',
        'type',
        'description',
    ];

    /** Cache TTL for allTyped() — 1 hour. */
    private const CACHE_KEY = 'app_config:all';
    private const CACHE_TTL = 3600;

    // ── Typed value accessor ──────────────────────────────────────────────────

    /**
     * Return the value cast to its declared type.
     */
    public function typedValue(): mixed
    {
        return match ($this->type) {
            'number'  => (float) $this->value,
            'boolean' => filter_var($this->value, FILTER_VALIDATE_BOOLEAN),
            'json'    => json_decode($this->value, true),
            default   => $this->value,
        };
    }

    // ── Static helpers ────────────────────────────────────────────────────────

    /**
     * Retrieve a config value by key (typed).
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $config = static::find($key);
        return $config ? $config->typedValue() : $default;
    }

    /**
     * Set a config value (updates existing) and invalidates cache.
     */
    public static function set(string $key, mixed $value): void
    {
        static::where('key', $key)->update(['value' => (string) $value]);
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Return all configs as a keyed array with typed values.
     * Result is cached for 1 hour; invalidated by set().
     */
    public static function allTyped(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return static::all()
                ->mapWithKeys(fn (AppConfig $c) => [$c->key => $c->typedValue()])
                ->all();
        });
    }

    /**
     * Manually flush the config cache (e.g. after bulk updates).
     */
    public static function flushCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
