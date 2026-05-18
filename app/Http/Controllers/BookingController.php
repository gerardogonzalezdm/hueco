<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookingRequest;
use App\Http\Requests\UpdateBookingRequest;
use App\Mail\BookingCancelledMail;
use App\Mail\BookingConfirmedMail;
use App\Models\Booking;
use App\Models\Space;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;

class BookingController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Bookings/Index', [
            'bookings' => Booking::with('space:id,name')
                ->orderByDesc('time_start')
                ->limit(200)
                ->get(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Bookings/Create', [
            'spaces' => Space::orderBy('name')->get([
                'id', 'name', 'duration_minutes', 'fixed_duration', 'price',
            ]),
        ]);
    }

    public function store(StoreBookingRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $admin = $request->user();
        $companyId = $admin->company_id;
        $start = Carbon::parse($data['time_start']);

        $end = ! empty($data['time_end'])
            ? Carbon::parse($data['time_end'])
            : $start->copy()->addMinutes((int) $data['duration_minutes']);

        // ¿El email coincide con un cliente ya existente de la empresa?
        $existingCustomer = User::query()
            ->withoutGlobalScopes()
            ->where('email', $data['client_email'])
            ->where('company_id', $companyId)
            ->where('role', 'customer')
            ->first();

        $customerId = $existingCustomer?->id;

        // Si admin marcó "crear cuenta" y NO existe el customer, crearlo
        if (! $existingCustomer && ! empty($data['create_account'])) {
            $newCustomer = User::create([
                'company_id' => $companyId,
                'role' => 'customer',
                'name' => $data['client_name'],
                'email' => $data['client_email'],
                'password' => Hash::make($data['account_password']),
                'email_verified_at' => now(),
            ]);
            $customerId = $newCustomer->id;
        }

        $booking = Booking::create([
            'space_id' => $data['space_id'],
            'user_id' => $customerId,
            'client_name' => $existingCustomer?->name ?? $data['client_name'],
            'client_email' => $data['client_email'],
            'client_phone' => $data['client_phone'] ?? null,
            'client_notes' => $data['client_notes'] ?? null,
            'time_start' => $start,
            'time_end' => $end,
            'status' => 'confirmed',
        ]);

        Mail::to($booking->client_email)
            ->send(new BookingConfirmedMail($booking->load('space', 'company')));

        $msg = 'Reserva creada correctamente.';
        if (! $existingCustomer && ! empty($data['create_account'])) {
            $msg .= ' Cuenta de cliente también creada.';
        }

        return redirect()
            ->route('bookings.index')
            ->with('success', $msg);
    }

    public function edit(Booking $booking): Response
    {
        return Inertia::render('Bookings/Edit', [
            'booking' => $booking->load('space:id,name'),
            'spaces' => Space::orderBy('name')->get([
                'id', 'name', 'duration_minutes', 'fixed_duration', 'price',
            ]),
        ]);
    }

    public function update(UpdateBookingRequest $request, Booking $booking): RedirectResponse
    {
        $data = $request->validated();
        $wasCancelled = $booking->status === 'cancelled';

        if (isset($data['time_start']) || isset($data['duration_minutes']) || isset($data['time_end'])) {
            $start = isset($data['time_start']) ? Carbon::parse($data['time_start']) : $booking->time_start;

            if (! empty($data['time_end'])) {
                $end = Carbon::parse($data['time_end']);
            } elseif (! empty($data['duration_minutes'])) {
                $end = $start->copy()->addMinutes((int) $data['duration_minutes']);
            } else {
                $end = $booking->time_end;
            }

            $data['time_start'] = $start;
            $data['time_end'] = $end;
            unset($data['duration_minutes']);
        }

        $booking->update($data);

        if (! $wasCancelled && $booking->status === 'cancelled') {
            Mail::to($booking->client_email)
                ->send(new BookingCancelledMail($booking->load('space', 'company')));
        }

        return redirect()
            ->route('bookings.index')
            ->with('success', 'Reserva actualizada.');
    }

    public function destroy(Booking $booking): RedirectResponse
    {
        if ($booking->status !== 'cancelled') {
            Mail::to($booking->client_email)
                ->send(new BookingCancelledMail($booking->load('space', 'company')));
        }

        $booking->delete();

        return redirect()
            ->route('bookings.index')
            ->with('success', 'Reserva eliminada.');
    }
}
