<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Http\Requests\Portal\StorePortalBookingRequest;
use App\Mail\BookingCancelledMail;
use App\Mail\BookingConfirmedMail;
use App\Models\Booking;
use App\Models\Space;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;

class BookingController extends Controller
{
    public function index(Request $request): Response
    {
        $bookings = Booking::withoutGlobalScopes()
            ->with('space:id,name')
            ->where('user_id', $request->user()->id)
            ->orderByDesc('time_start')
            ->get();

        return Inertia::render('Portal/Bookings/Index', [
            'bookings' => $bookings,
        ]);
    }

    public function create(Request $request): Response
    {
        $spaces = Space::query()
            ->withoutGlobalScopes()
            ->where('company_id', $request->user()->company_id)
            ->orderBy('name')
            ->get(['id', 'name', 'duration_minutes', 'fixed_duration', 'price', 'show_price', 'show_duration']);

        return Inertia::render('Portal/Bookings/Create', [
            'spaces' => $spaces,
        ]);
    }

    public function store(StorePortalBookingRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $user = $request->user();
        $start = Carbon::parse($data['time_start']);

        $end = ! empty($data['time_end'])
            ? Carbon::parse($data['time_end'])
            : $start->copy()->addMinutes((int) $data['duration_minutes']);

        $booking = Booking::create([
            'company_id' => $user->company_id,
            'space_id' => $data['space_id'],
            'user_id' => $user->id,
            'client_name' => $user->name,
            'client_email' => $user->email,
            'client_phone' => $data['client_phone'] ?? null,
            'client_notes' => $data['client_notes'] ?? null,
            'time_start' => $start,
            'time_end' => $end,
            'status' => 'confirmed',
        ]);

        Mail::to($booking->client_email)
            ->send(new BookingConfirmedMail($booking->load('space', 'company')));

        return redirect()
            ->route('portal.bookings.show', $booking->id)
            ->with('success', 'Reserva creada correctamente.');
    }

    public function show(Request $request, Booking $booking): Response
    {
        abort_unless($booking->user_id === $request->user()->id, 404);

        return Inertia::render('Portal/Bookings/Show', [
            'booking' => $booking->load('space:id,name,duration_minutes', 'company:id,name'),
        ]);
    }

    public function cancel(Request $request, Booking $booking): RedirectResponse
    {
        abort_unless($booking->user_id === $request->user()->id, 404);

        if ($booking->status !== 'cancelled') {
            $booking->update(['status' => 'cancelled']);

            Mail::to($booking->client_email)
                ->send(new BookingCancelledMail($booking->load('space', 'company')));
        }

        return redirect()
            ->route('portal.bookings.show', $booking->id)
            ->with('success', 'Reserva cancelada.');
    }
}
