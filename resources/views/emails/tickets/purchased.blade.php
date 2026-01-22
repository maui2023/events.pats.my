<x-mail::message>
# Tiket Anda: {{ $event->title }}

Hai {{ $attendee->name }},

Terima kasih kerana mendaftar untuk acara **{{ $event->title }}**.

Berikut adalah pautan untuk tiket QR anda:

<x-mail::button :url="$url">
Lihat Tiket QR
</x-mail::button>

Sila tunjukkan kod QR ini semasa pendaftaran acara.

Terima kasih,<br>
{{ config('app.name') }}
</x-mail::message>
