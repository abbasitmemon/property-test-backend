@component('mail::message')
# Hello {{ $booking->guest_name }},

Your booking for **{{ $booking->property->title }}** has been updated.

**New Status:** {{ ucfirst($booking->status) }}
**Dates:** {{ $booking->start_date }} → {{ $booking->end_date }}

@component('mail::button', ['url' => url('/')])
View Details
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent