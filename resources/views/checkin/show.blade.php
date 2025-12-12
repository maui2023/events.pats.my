<h2>Status Check-in</h2>
@if($status === 'invalid')
    <p>Kod tidak sah.</p>
@elseif($status === 'used')
    <p>Kod telah digunakan.</p>
@else
    <form method="post" action="{{ route('checkin.scan', $attendee->qr_code) }}">
        @csrf
        <button type="submit">Sahkan Kehadiran</button>
    </form>
@endif

