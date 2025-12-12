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
        $attendee = Attendee::query()->where('qr_code', $code)->first();
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
        $attendee = Attendee::query()->where('qr_code', $code)->firstOrFail();
        if (!$attendee->checked_in_at) {
            Checkin::create([
                'attendee_id' => $attendee->id,
                'status' => 'valid',
                'scanned_at' => Carbon::now(),
            ]);
            $attendee->update(['checked_in_at' => Carbon::now()]);
        }
        return redirect()->route('checkin.show', [$code]);
    }
}

