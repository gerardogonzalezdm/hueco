<x-mail::message>
# Tu reserva está confirmada ✅

Hola {{ $booking->client_name }},

Tu reserva ha sido **confirmada**. Te resumimos los detalles:

<x-mail::panel>
**Espacio:** {{ $space?->name ?? '—' }}
**Fecha:** {{ $booking->time_start->locale('es')->isoFormat('dddd D [de] MMMM [de] YYYY') }}
**Horario:** {{ $booking->time_start->format('H:i') }} – {{ $booking->time_end->format('H:i') }}
@if($booking->client_phone)
**Teléfono de contacto:** {{ $booking->client_phone }}
@endif
</x-mail::panel>

@if($booking->client_notes)
**Notas adicionales:**

{{ $booking->client_notes }}
@endif

@if($booking->manage_token)
<x-mail::button :url="route('public.bookings.show', $booking->manage_token)" color="primary">
Ver o cancelar mi reserva
</x-mail::button>
@else
Si necesitas modificar o cancelar tu reserva, ponte en contacto con nosotros respondiendo a este correo.
@endif

Un saludo,
**{{ $company?->name ?? config('app.name') }}**
</x-mail::message>
