<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
     * Set a config value (creates or updates).
     */
    public static function set(string $key, mixed $value): void
    {
        static::where('key', $key)->update(['value' => (string) $value]);
    }

    /**
     * Return all configs as a keyed array with typed values.
     */
    public static function allTyped(): array
    {
        return static::all()->mapWithKeys(
            fn (AppConfig $c) => [$c->key => $c->typedValue()]
        )->all();
    }
}
