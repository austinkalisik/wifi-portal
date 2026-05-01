<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WifiPackage extends Model
{
    protected $fillable = [
        'business_id',
        'name',
        'access_type',
        'description',
        'duration_minutes',
        'ad_watch_seconds',
        'price',
        'download_mbps',
        'upload_mbps',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'price' => 'decimal:2',
        'ad_watch_seconds' => 'integer',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }
}
