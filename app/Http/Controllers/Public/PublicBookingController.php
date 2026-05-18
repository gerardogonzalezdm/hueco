<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\Public\StorePublicBookingRequest;
use App\Mail\BookingCancelledMail;
use App\Mail\BookingConfirmedMail;
use App\Models\Booking;
use App\Models\Company;
use App\Models\Space;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class PublicBookingController extends Controller
{
    /**
     * Formulario público de reserva.
     */
    public function create(Company $company): Response
    {
        $spaces = Space::query()
            ->withoutGlobalScopes()
            ->where('company_id', $company->id)
            ->orderBy('name')
            ->get(['id', 'name', 'duration_minutes', 'fixed_duration', 'price', 'show_price', 'show_duration']);

        return Inertia::render('Public/BookingForm', [
            'company' => ['name' => $company->name, 'slug' => $company->slug],
            'spaces' => $spaces,
        ]);
    }

    /**
     * Almacena una reserva creada desde el portal público.
     */
    public function store(StorePublicBookingRequest $request, Company $company): RedirectResponse
    {
        $data = $request->validated();
        $start = Carbon::parse($data['time_start']);

        $booking = Booking::create([
            'company_id' => $company->id,
            'space_id' => $data['space_id'],
            'user_id' => null,
            'client_name' => $data['client_name'],
            'client_email' => $data['client_email'],
            'client_phone' => $data['client_phone'] ?? null,
            'client_notes' => $data['client_notes'] ?? null,
            'time_start' => $start,
            'time_end' => $start->copy()->addMinutes((int) $data['duration_minutes']),
            'status' => 'confirmed',
            'manage_token' => Str::random(48),
        ]);

        Mail::to($booking->client_email)
            ->send(new BookingConfirmedMail($booking->load('space', 'company')));

        return redirect()->route('public.bookings.show', $booking->manage_token);
    }

    /**
     * Vista de gestión de reserva (acceso por token único, sin login).
     */
    public function show(string $token): Response
    {
        $booking = Booking::withoutGlobalScopes()
            ->with('space:id,name', 'company:id,name,slug')
            ->where('manage_token', $token)
            ->firstOrFail();

        return Inertia::render('Public/MyBooking', [
            'booking' => [
                'id' => $booking->id,
                'client_name' => $booking->client_name,
                'client_email' => $booking->client_email,
                'client_phone' => $booking->client_phone,
                'time_start' => $booking->time_start->toIso8601String(),
                'time_end' => $booking->time_end->toIso8601String(),
                'status' => $booking->status,
                'space' => $booking->space,
                'company' => $booking->company,
                'manage_token' => $booking->manage_token,
            ],
        ]);
    }

    /**
     * Permite al cliente cancelar su reserva con el token.
     */
    public function cancel(string $token): RedirectResponse
    {
        $booking = Booking::withoutGlobalScopes()
            ->where('manage_token', $token)
            ->firstOrFail();

        if ($booking->status !== 'cancelled') {
            $booking->update(['status' => 'cancelled']);

            Mail::to($booking->client_email)
                ->send(new BookingCancelledMail($booking->load('space', 'company')));
        }

        return redirect()->route('public.bookings.show', $token);
    }
}
