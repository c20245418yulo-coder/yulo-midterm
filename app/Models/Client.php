<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    protected $fillable = [
        'client_name',
        'client_email',
        'client_phone',
    ];

    public function viewingAppointments(): HasMany
    {
        return $this->hasMany(ViewingAppointment::class);
    }

    public function propertyInquiries(): HasMany
    {
        return $this->hasMany(PropertyInquiry::class);
    }
}