<?php

namespace App\Http\Controllers;

use App\Models\Attendee;
use App\Models\Checkin;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class CheckinController extends Controller
{
    public function show(string $code)
    {
        $attendee = Attendee::with('event')->where('qr_code', $code)->firstOrFail();
        
        // Authorization Check
        $this->authorizeScanner($attendee->event);

        $status = 'invalid';
        if ($attendee) {
            if ($attendee->checked_in_at) {
                $status = 'used';
            } else {
                $status = 'valid';
            }
        }
        return view('checkin.show', compact('attendee', 'status'));
    }

    public function scan(string $code)
    {
        $attendee = Attendee::with('event')->where('qr_code', $code)->firstOrFail();
        
        // Authorization Check
        $this->authorizeScanner($attendee->event);

        if (!$attendee->checked_in_at) {
            Checkin::create([
                'attendee_id' => $attendee->id,
                'status' => 'valid',
                'scanned_at' => Carbon::now(),
            ]);
            $attendee->update(['checked_in_at' => Carbon::now()]);
        }
        return redirect()->route('checkin.show', ['code' => $code, 'return_to' => request('return_to')]);
    }

    public function scanner(string $slug)
    {
        $event = \App\Models\Event::where('slug', $slug)->firstOrFail();
        $this->authorizeScanner($event);
        return view('events.scan', compact('event'));
    }

    private function authorizeScanner($event)
    {
        if (!auth()->check()) {
            abort(403, 'Sila log masuk untuk mengimbas tiket.');
        }

        $user = auth()->user();
        
        // Allow if user is the organizer
        if ($event->organizer_id === $user->id) {
            return true;
        }

        // Allow if user is assigned staff
        $isStaff = \App\Models\EventStaff::where('event_id', $event->id)
                    ->where('user_id', $user->id)
                    ->exists();
        
        if ($isStaff) {
            return true;
        }

        abort(403, 'Anda tidak mempunyai kebenaran untuk mengimbas tiket acara ini.');
    }
}

