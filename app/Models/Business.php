<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Business extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_name',
        'site_name',
        'contact_email',
        'phone',
        'timezone',
        'currency',
        'api_key',
    ];

    public static function booted(): void
    {
        static::creating(function (Business $business): void {
            $business->api_key ??= 'wp_'.Str::random(40);
        });
    }

    public function brandingProfile(): HasOne
    {
        return $this->hasOne(BrandingProfile::class);
    }

    public function packages(): HasMany
    {
        return $this->hasMany(WifiPackage::class);
    }

    public function routers(): HasMany
    {
        return $this->hasMany(Router::class);
    }

    public function devices(): HasMany
    {
        return $this->hasMany(CustomerDevice::class);
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(GuestSession::class);
    }
}
