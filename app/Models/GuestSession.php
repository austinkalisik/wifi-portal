<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GuestSession extends Model
{
    protected $fillable = [
        'business_id',
        'router_id',
        'wifi_package_id',
        'access_method',
        'guest_name',
        'email',
        'phone',
        'payment_reference',
        'amount_paid',
        'device_mac',
        'status',
        'started_at',
        'expires_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'expires_at' => 'datetime',
        'amount_paid' => 'decimal:2',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function router(): BelongsTo
    {
        return $this->belongsTo(Router::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(WifiPackage::class, 'wifi_package_id');
    }
}
