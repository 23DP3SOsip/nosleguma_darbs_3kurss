<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CarMaintenanceLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'car_id',
        'user_id',
        'maintenance_type',
        'description',
        'performed_at',
        'mileage',
        'cost',
    ];

    protected function casts(): array
    {
        return [
            'performed_at' => 'datetime',
            'mileage' => 'integer',
            'cost' => 'decimal:2',
        ];
    }

    public function car(): BelongsTo
    {
        return $this->belongsTo(Car::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}