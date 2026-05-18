<?php

namespace App\Http\Requests\Portal;

use App\Models\Booking;
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
            'duration_minutes' => ['required', 'integer', 'min:5', 'max:1440'],
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

            $start = Carbon::parse($this->input('time_start'));
            $end = $start->copy()->addMinutes((int) $this->input('duration_minutes'));

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
