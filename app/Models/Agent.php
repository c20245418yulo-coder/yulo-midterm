<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Agent extends Model
{
    protected $fillable = ['agent_name'];

    public function properties(): HasMany
    {
        return $this->hasMany(Property::class);
    }

    public function viewingAppointments(): HasMany
    {
        return $this->hasMany(ViewingAppointment::class);
    }
}