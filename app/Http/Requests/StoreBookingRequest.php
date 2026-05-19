<?php

namespace App\Http\Requests;

use App\Models\Booking;
use App\Models\Space;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $companyId = $this->user()->company_id;

        return [
            'space_id' => [
                'required',
                Rule::exists('spaces', 'id')->where('company_id', $companyId),
            ],
            'client_name' => ['required', 'string', 'max:255'],
            'client_email' => ['required', 'email', 'max:255'],
            'client_phone' => ['nullable', 'string', 'max:50'],
            'client_notes' => ['nullable', 'string', 'max:1000'],
            'time_start' => ['required', 'date'],
            // Para espacios con duración fija: enviar duration_minutes (mínimo 30).
            // Para espacios flex: enviar time_end directamente (mínimo 60 min de rango).
            'duration_minutes' => ['nullable', 'integer', 'min:30', 'max:1440'],
            'time_end' => ['nullable', 'date', 'after:time_start'],
            // Crear cuenta de cliente al vuelo (admin presencial)
            'create_account' => ['nullable', 'boolean'],
            'account_password' => ['nullable', 'required_if:create_account,true', 'string', 'min:8'],
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
                    'Indica la duración de la reserva o la hora de fin.'
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

            if ($space && ! $space->fixed_duration) {
                if ($start->diffInMinutes($end) < 60) {
                    $validator->errors()->add(
                        'time_end',
                        'En espacios con duración libre la reserva debe durar al menos 1 hora.'
                    );

                    return;
                }
            }

            // Si quiere crear cuenta, el email no debe estar ya en uso
            if ($this->boolean('create_account')) {
                $existing = User::query()
                    ->withoutGlobalScopes()
                    ->where('email', $this->input('client_email'))
                    ->exists();
                if ($existing) {
                    $validator->errors()->add(
                        'client_email',
                        'Este email ya está registrado. Desmarca "Crear cuenta" para asociar la reserva al cliente existente.'
                    );

                    return;
                }
            }

            // Detección de conflictos horarios
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
                    'Ya existe una reserva en este espacio que solapa con el horario seleccionado.'
                );
            }
        });
    }
}
