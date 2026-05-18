<?php

namespace App\Http\Requests\Public;

use App\Models\Booking;
use App\Models\Company;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePublicBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var Company $company */
        $company = $this->route('company');

        return [
            'space_id' => [
                'required',
                Rule::exists('spaces', 'id')->where('company_id', $company->id),
            ],
            'client_name' => ['required', 'string', 'max:255'],
            'client_email' => ['required', 'email', 'max:255'],
            'client_phone' => ['nullable', 'string', 'max:50'],
            'client_notes' => ['nullable', 'string', 'max:1000'],
            'time_start' => ['required', 'date', 'after:now'],
            'duration_minutes' => ['required', 'integer', 'min:5', 'max:1440'],
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
