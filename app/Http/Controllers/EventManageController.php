<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Order;
use App\Models\Attendee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EventManageController extends Controller
{
    public function index(Request $request, string $slug)
    {
        if (!Auth::check()) {
            return redirect()->guest('/login');
        }

        $event = Event::where('slug', $slug)->firstOrFail();

        // Check if user is organizer or staff
        $isOrganizer = $event->organizer_id === Auth::id();
        $isStaff = $event->staffs()->where('user_id', Auth::id())->exists();

        if (!$isOrganizer && !$isStaff) {
            abort(403);
        }

        $query = Order::with(['attendees', 'ticket'])
            ->where('event_id', $event->id)
            ->orderByDesc('created_at');

        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('buyer_name', 'like', "%{$search}%")
                  ->orWhere('buyer_email', 'like', "%{$search}%")
                  ->orWhereHas('attendees', function($aq) use ($search) {
                      $aq->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->has('status')) {
            $status = $request->get('status');
            if ($status !== 'all') {
                $query->where('status', $status);
            }
        }

        $orders = $query->paginate(20)->withQueryString();

        return view('events.manage', compact('event', 'orders'));
    }

    public function destroyOrder(Request $request, string $slug, Order $order)
    {
        if (!Auth::check()) {
            abort(403);
        }

        $event = Event::where('slug', $slug)->firstOrFail();
        
        // Ownership check
        $isOrganizer = $event->organizer_id === Auth::id();
        $isStaff = $event->staffs()->where('user_id', Auth::id())->exists();

        if (!$isOrganizer && !$isStaff) {
            abort(403);
        }

        if ($order->event_id !== $event->id) {
            abort(404);
        }

        // If order was paid (or free), it occupied a slot. Release it.
        if ($order->status === 'paid') {
            $ticket = $order->ticket;
            if ($ticket) {
                // Decrement sold count safely
                $newSold = max(0, $ticket->sold - $order->quantity);
                $ticket->update(['sold' => $newSold]);
            }
        }

        // Delete attendees and order
        $order->attendees()->delete();
        $order->delete();

        return back()->with('success', 'Pesanan berjaya dipadam.');
    }

    public function showCertificate(string $uuid)
    {
        $attendee = Attendee::where('qr_code', $uuid)->with('event')->firstOrFail();

        if (!$attendee->checked_in_at) {
            abort(404, 'Sijil belum tersedia (Peserta belum hadir).');
        }

        return view('certificates.default', compact('attendee'));
    }
}
