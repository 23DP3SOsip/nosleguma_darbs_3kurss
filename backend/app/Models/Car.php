<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Car extends Model
{
    use HasFactory;

    public const STATUS_AVAILABLE = 'available';

    public const STATUS_MAINTENANCE = 'maintenance';

    protected $fillable = [
        'brand',
        'model',
        'plate_number',
        'transmission_type',
        'image_url',
        'status',
    ];

    public function activeReservation(): HasOne
    {
        return $this->hasOne(CarReservation::class)->where('status', CarReservation::STATUS_ACTIVE);
    }

    public function maintenanceLogs(): HasMany
    {
        return $this->hasMany(CarMaintenanceLog::class);
    }
}