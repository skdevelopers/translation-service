<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
