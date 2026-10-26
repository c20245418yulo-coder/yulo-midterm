<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ViewingAppointment extends Model
{
    protected $fillable = [
        'client_id',
        'property_id',
        'agent_id',
        'viewing_date',
        'viewing_time',
        'client_feedback',
        'viewing_status',
    ];

    protected function casts(): array
    {
        return [
            'viewing_date' => 'date',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }
}