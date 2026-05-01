<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BrandingProfile extends Model
{
    protected $fillable = [
        'business_id',
        'logo_url',
        'primary_color',
        'accent_color',
        'welcome_headline',
        'welcome_message',
        'terms_url',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }
}
