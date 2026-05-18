<x-mail::message>
# Tu reserva ha sido cancelada

Hola {{ $booking->client_name }},

Te informamos de que la siguiente reserva ha sido **cancelada**:

<x-mail::panel>
**Espacio:** {{ $space?->name ?? '—' }}
**Fecha:** {{ $booking->time_start->locale('es')->isoFormat('dddd D [de] MMMM [de] YYYY') }}
**Horario:** {{ $booking->time_start->format('H:i') }} – {{ $booking->time_end->format('H:i') }}
</x-mail::panel>

Si la cancelación no era esperada o tienes cualquier duda, responde a este correo y resolveremos lo que necesites.

Un saludo,
**{{ $company?->name ?? config('app.name') }}**
</x-mail::message>
