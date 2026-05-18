<?php

namespace App\Http\Requests;

use App\Models\Booking;
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
            'duration_minutes' => ['sometimes', 'required', 'integer', 'min:5', 'max:1440'],
            'status' => ['sometimes', 'required', Rule::in(['pending', 'confirmed', 'cancelled'])],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            if (! $this->has('time_start') && ! $this->has('duration_minutes') && ! $this->has('space_id')) {
                return;
            }

            $booking = $this->route('booking');
            $spaceId = $this->input('space_id', $booking->space_id);
            $start = $this->has('time_start')
                ? Carbon::parse($this->input('time_start'))
                : $booking->time_start;
            $end = $this->has('duration_minutes')
                ? $start->copy()->addMinutes((int) $this->input('duration_minutes'))
                : $booking->time_end;

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
