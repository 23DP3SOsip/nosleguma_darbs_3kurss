<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Car extends Model
{
    use HasFactory;

    protected $fillable = [
        'brand',
        'model',
        'plate_number',
        'transmission_type',
        'image_url',
    ];

    public function activeReservation(): HasOne
    {
        return $this->hasOne(CarReservation::class)->where('status', CarReservation::STATUS_ACTIVE);
    }
}