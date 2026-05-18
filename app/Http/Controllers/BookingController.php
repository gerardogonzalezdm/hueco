<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookingRequest;
use App\Http\Requests\UpdateBookingRequest;
use App\Mail\BookingCancelledMail;
use App\Mail\BookingConfirmedMail;
use App\Models\Booking;
use App\Models\Space;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
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
            'spaces' => Space::orderBy('name')->get(['id', 'name', 'duration_minutes', 'fixed_duration', 'price']),
        ]);
    }

    public function store(StoreBookingRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $start = Carbon::parse($data['time_start']);

        $booking = Booking::create([
            'space_id' => $data['space_id'],
            'user_id' => $request->user()->id,
            'client_name' => $data['client_name'],
            'client_email' => $data['client_email'],
            'client_phone' => $data['client_phone'] ?? null,
            'client_notes' => $data['client_notes'] ?? null,
            'time_start' => $start,
            'time_end' => $start->copy()->addMinutes((int) $data['duration_minutes']),
            'status' => 'confirmed',
        ]);

        Mail::to($booking->client_email)
            ->send(new BookingConfirmedMail($booking->load('space', 'company')));

        return redirect()
            ->route('bookings.index')
            ->with('success', 'Reserva creada correctamente. Confirmación enviada al cliente.');
    }

    public function edit(Booking $booking): Response
    {
        return Inertia::render('Bookings/Edit', [
            'booking' => $booking->load('space:id,name'),
            'spaces' => Space::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(UpdateBookingRequest $request, Booking $booking): RedirectResponse
    {
        $data = $request->validated();
        $wasCancelled = $booking->status === 'cancelled';

        if (isset($data['time_start']) || isset($data['duration_minutes'])) {
            $start = Carbon::parse($data['time_start'] ?? $booking->time_start);
            $duration = (int) ($data['duration_minutes']
                ?? $booking->time_start->diffInMinutes($booking->time_end));
            $data['time_start'] = $start;
            $data['time_end'] = $start->copy()->addMinutes($duration);
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
