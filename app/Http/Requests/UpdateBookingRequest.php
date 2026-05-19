<?php

namespace App\Http\Requests;

use App\Models\Booking;
use App\Models\Space;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        $companyId = $this->user()->company_id;

        return [
            'space_id' => [
                'sometimes',
                'required',
                Rule::exists('spaces', 'id')->where('company_id', $companyId),
            ],
            'client_name' => ['sometimes', 'required', 'string', 'max:255'],
            'client_email' => ['sometimes', 'required', 'email', 'max:255'],
            'client_phone' => ['sometimes', 'nullable', 'string', 'max:50'],
            'client_notes' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'time_start' => ['sometimes', 'required', 'date'],
            'duration_minutes' => ['sometimes', 'nullable', 'integer', 'min:30', 'max:1440'],
            'time_end' => ['sometimes', 'nullable', 'date'],
            'status' => ['sometimes', 'required', Rule::in(['pending', 'confirmed', 'cancelled'])],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            if (! $this->has('time_start') && ! $this->has('duration_minutes') && ! $this->has('time_end') && ! $this->has('space_id')) {
                return;
            }

            $booking = $this->route('booking');
            $spaceId = $this->input('space_id', $booking->space_id);

            $start = $this->has('time_start')
                ? Carbon::parse($this->input('time_start'))
                : $booking->time_start;

            if ($this->filled('time_end')) {
                $end = Carbon::parse($this->input('time_end'));
            } elseif ($this->filled('duration_minutes')) {
                $end = $start->copy()->addMinutes((int) $this->input('duration_minutes'));
            } else {
                $end = $booking->time_end;
            }

            if ($end <= $start) {
                $validator->errors()->add(
                    'time_end',
                    'La hora de fin debe ser posterior a la de inicio.'
                );

                return;
            }

            // Mínimo 1 hora en reservas sobre espacios con duración flex.
            $space = Space::query()
                ->withoutGlobalScopes()
                ->find($spaceId);

            if ($space && ! $space->fixed_duration && $start->diffInMinutes($end) < 60) {
                $validator->errors()->add(
                    'time_end',
                    'En espacios con duración libre la reserva debe durar al menos 1 hora.'
                );

                return;
            }

            $hasConflict = Booking::query()
                ->withoutGlobalScopes()
                ->where('id', '!=', $booking->id)
                ->where('space_id', $spaceId)
                ->where('status', '!=', 'cancelled')
                ->where('time_start', '<', $end)
                ->where('time_end', '>', $start)
                ->exists();

            if ($hasConflict) {
                $validator->errors()->add(
                    'time_start',
                    'Ya existe una reserva en este espacio que solapa con el horario seleccionado.'
                );
            }
        });
    }
}
