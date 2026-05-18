<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'company_id',
    'space_id',
    'user_id',
    'client_name',
    'client_email',
    'client_phone',
    'client_notes',
    'time_start',
    'time_end',
    'status',
    'google_event_id',
    'manage_token',
])]
class Booking extends Model
{
    use BelongsToCompany, HasFactory;

    protected function casts(): array
    {
        return [
            'time_start' => 'datetime',
            'time_end' => 'datetime',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function space(): BelongsTo
    {
        return $this->belongsTo(Space::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
