<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateBookingRequest;
use App\Models\Booking;
use App\Models\Space;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CalendarController extends Controller
{
    /**
     * Renders the calendar page with bookings + spaces preloaded.
     */
    public function index(): Response
    {
        $spaces = Space::orderBy('name')->get(['id', 'name']);

        $bookings = Booking::with('space:id,name')
            ->where('status', '!=', 'cancelled')
            ->get()
            ->map(fn (Booking $b) => [
                'id' => $b->id,
                'start' => $b->time_start->format('Y-m-d H:i'),
                'end' => $b->time_end->format('Y-m-d H:i'),
                'title' => $b->client_name,
                'space_id' => $b->space_id,
                'space_name' => $b->space?->name,
                'status' => $b->status,
            ]);

        return Inertia::render('Calendar', [
            'spaces' => $spaces,
            'bookings' => $bookings,
        ]);
    }

    /**
     * Endpoint called by drag-and-drop in the calendar.
     * Reuses UpdateBookingRequest validation (incl. conflict detection).
     */
    public function reschedule(UpdateBookingRequest $request, Booking $booking): RedirectResponse
    {
        $data = $request->validated();

        if (isset($data['time_start']) || isset($data['duration_minutes'])) {
            $start = Carbon::parse($data['time_start'] ?? $booking->time_start);
            $duration = (int) ($data['duration_minutes']
                ?? $booking->time_start->diffInMinutes($booking->time_end));
            $data['time_start'] = $start;
            $data['time_end'] = $start->copy()->addMinutes($duration);
            unset($data['duration_minutes']);
        }

        $booking->update($data);

        return back();
    }
}
