<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'company_id',
    'name',
    'slug',
    'duration_minutes',
    'fixed_duration',
    'price',
    'show_price',
    'show_duration',
])]
class Space extends Model
{
    use BelongsToCompany, HasFactory;

    protected function casts(): array
    {
        return [
            'fixed_duration' => 'boolean',
            'show_price' => 'boolean',
            'show_duration' => 'boolean',
            'price' => 'decimal:2',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
}
