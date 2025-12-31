<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class FieldLabel extends Model
{
    protected $fillable = [
        'field_name',
        'field_value',
        'label',
        'category',
        'order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer',
    ];

    /**
     * Get label for a specific field value with caching
     */
    public static function getLabel(string $fieldName, string $fieldValue): ?string
    {
        return Cache::remember(
            "field_label_{$fieldName}_{$fieldValue}",
            now()->addDay(),
            function () use ($fieldName, $fieldValue) {
                return self::where('field_name', $fieldName)
                    ->where('field_value', $fieldValue)
                    ->where('is_active', true)
                    ->value('label');
            }
        );
    }

    /**
     * Get all labels for a specific field with caching
     */
    public static function getFieldLabels(string $fieldName): array
    {
        return Cache::remember(
            "field_labels_{$fieldName}",
            now()->addDay(),
            function () use ($fieldName) {
                return self::where('field_name', $fieldName)
                    ->where('is_active', true)
                    ->orderBy('order')
                    ->pluck('label', 'field_value')
                    ->toArray();
            }
        );
    }

    /**
     * Clear label cache
     */
    public static function clearCache(?string $fieldName = null): void
    {
        if ($fieldName) {
            Cache::forget("field_labels_{$fieldName}");
            // Clear individual label caches for this field
            $values = self::where('field_name', $fieldName)->pluck('field_value');
            foreach ($values as $value) {
                Cache::forget("field_label_{$fieldName}_{$value}");
            }
        } else {
            Cache::flush();
        }
    }

    /**
     * Boot method to clear cache on model changes
     */
    protected static function boot()
    {
        parent::boot();

        static::saved(function ($model) {
            self::clearCache($model->field_name);
        });

        static::deleted(function ($model) {
            self::clearCache($model->field_name);
        });
    }
}

