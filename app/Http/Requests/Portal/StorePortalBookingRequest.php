<?php

namespace App\Http\Requests\Portal;

use App\Models\Booking;
use App\Models\Space;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePortalBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isCustomer() ?? false;
    }

    public function rules(): array
    {
        $companyId = $this->user()->company_id;

        return [
            'space_id' => [
                'required',
                Rule::exists('spaces', 'id')->where('company_id', $companyId),
            ],
            'time_start' => ['required', 'date', 'after:now'],
            // Espacios con duración fija → duration_minutes (mín. 30). Flex → time_end (mín. 60 min de rango).
            'duration_minutes' => ['nullable', 'integer', 'min:30', 'max:1440'],
            'time_end' => ['nullable', 'date', 'after:time_start'],
            'client_phone' => ['nullable', 'string', 'max:50'],
            'client_notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            if (! $this->filled('time_end') && ! $this->filled('duration_minutes')) {
                $validator->errors()->add(
                    'duration_minutes',
                    'Indica la duración o la hora de fin.'
                );

                return;
            }

            $start = Carbon::parse($this->input('time_start'));
            $end = $this->filled('time_end')
                ? Carbon::parse($this->input('time_end'))
                : $start->copy()->addMinutes((int) $this->input('duration_minutes'));

            // Mínimo 1 hora en reservas sobre espacios con duración flex.
            $space = Space::query()
                ->withoutGlobalScopes()
                ->find($this->input('space_id'));

            if ($space && ! $space->fixed_duration && $start->diffInMinutes($end) < 60) {
                $validator->errors()->add(
                    'time_end',
                    'En espacios con duración libre la reserva debe durar al menos 1 hora.'
                );

                return;
            }

            $hasConflict = Booking::query()
                ->withoutGlobalScopes()
                ->where('space_id', $this->input('space_id'))
                ->where('status', '!=', 'cancelled')
                ->where('time_start', '<', $end)
                ->where('time_end', '>', $start)
                ->exists();

            if ($hasConflict) {
                $validator->errors()->add(
                    'time_start',
                    'Ese horario ya no está disponible. Elige otro.'
                );
            }
        });
    }
}
