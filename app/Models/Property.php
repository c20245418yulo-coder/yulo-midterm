<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Property extends Model
{
    protected $fillable = [
        'property_code',
        'property_type',
        'listing_price',
        'agent_id',
        'viewing_count',
    ];

    protected function casts(): array
    {
        return [
            'listing_price' => 'decimal:2',
            'viewing_count' => 'integer',
        ];
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    public function viewingAppointments(): HasMany
    {
        return $this->hasMany(ViewingAppointment::class);
    }

    public function propertyInquiries(): HasMany
    {
        return $this->hasMany(PropertyInquiry::class);
    }
}