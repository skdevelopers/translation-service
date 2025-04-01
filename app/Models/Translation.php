<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Class Translation
 *
 * @property int $id
 * @property string $locale
 * @property string $key
 * @property string $value
 * @property array|null $tags
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Translation extends Model
{
    use HasFactory;

    protected $fillable = ['locale', 'key', 'value', 'tags'];

    protected $casts = [
        'tags' => 'array',
    ];

    protected static function booted()
    {
        static::saved(function () {
            Cache::forget('translations_export');
            if (file_exists(storage_path('app/translations_export.json'))) {
                unlink(storage_path('app/translations_export.json'));
            }
        });

        static::deleted(function () {
            Cache::forget('translations_export');
            if (file_exists(storage_path('app/translations_export.json'))) {
                unlink(storage_path('app/translations_export.json'));
            }
        });
    }
}
