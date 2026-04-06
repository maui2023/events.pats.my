<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Event;
use App\Models\Profile;
use App\Models\Wallet;
use App\Services\AyuWalletService;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->guest('/login');
        }

        $user = Auth::user();
        $events = Event::query()
            ->where('organizer_id', $user->id)
            ->orderByDesc('start_at')
            ->limit(10)
            ->get();

        $profile = Profile::where('user_id', $user->id)->first();
        $wallet = Wallet::where('user_id', $user->id)->first();

        if ($wallet && $wallet->wallet_id) {
            try {
                $svc = new AyuWalletService();
                $wallet->credit_balance = $svc->getBalance($wallet->wallet_id);
                $wallet->save();
            } catch (\Throwable $e) {
            }
        }

        $now = Carbon::now();

        $rsvpSearch = trim((string) $request->query('rsvp_search', ''));
        $attendees = \App\Models\Attendee::query()
            ->where('email', $user->email)
            ->with('event')
            ->when($rsvpSearch !== '', function ($q) use ($rsvpSearch) {
                $q->where(function ($qq) use ($rsvpSearch) {
                    $qq->where('name', 'like', '%' . $rsvpSearch . '%')
                        ->orWhere('email', 'like', '%' . $rsvpSearch . '%')
                        ->orWhereHas('event', function ($qe) use ($rsvpSearch) {
                            $qe->where('title', 'like', '%' . $rsvpSearch . '%')
                                ->orWhere('location', 'like', '%' . $rsvpSearch . '%');
                        });
                });
            })
            ->orderByDesc('id')
            ->paginate(5, ['*'], 'rsvp_page')
            ->withQueryString();

        $ordersSearch = trim((string) $request->query('orders_search', ''));
        $orders = \App\Models\Order::query()
            ->where('buyer_email', $user->email)
            ->with(['event', 'ticket'])
            ->when($ordersSearch !== '', function ($q) use ($ordersSearch) {
                $q->where(function ($qq) use ($ordersSearch) {
                    $qq->where('status', 'like', '%' . $ordersSearch . '%')
                        ->orWhere('buyer_name', 'like', '%' . $ordersSearch . '%')
                        ->orWhereHas('event', function ($qe) use ($ordersSearch) {
                            $qe->where('title', 'like', '%' . $ordersSearch . '%')
                                ->orWhere('location', 'like', '%' . $ordersSearch . '%');
                        })
                        ->orWhereHas('ticket', function ($qt) use ($ordersSearch) {
                            $qt->where('name', 'like', '%' . $ordersSearch . '%');
                        });
                });
            })
            ->orderByDesc('id')
            ->paginate(5, ['*'], 'orders_page')
            ->withQueryString();

        $attendedSearch = trim((string) $request->query('attended_search', ''));
        $pastAttended = \App\Models\Attendee::query()
            ->where('email', $user->email)
            ->whereNotNull('checked_in_at')
            ->with('event')
            ->whereHas('event', function ($q) use ($now) {
                $q->where('is_published', true)
                    ->where(function ($qq) use ($now) {
                        $qq->whereNotNull('end_at')->where('end_at', '<', $now)
                            ->orWhere(function ($qqq) use ($now) {
                                $qqq->whereNull('end_at')->where('start_at', '<', $now);
                            });
                    });
            })
            ->when($attendedSearch !== '', function ($q) use ($attendedSearch) {
                $q->where(function ($qq) use ($attendedSearch) {
                    $qq->where('name', 'like', '%' . $attendedSearch . '%')
                        ->orWhereHas('event', function ($qe) use ($attendedSearch) {
                            $qe->where('title', 'like', '%' . $attendedSearch . '%')
                                ->orWhere('location', 'like', '%' . $attendedSearch . '%');
                        });
                });
            })
            ->orderByDesc('checked_in_at')
            ->paginate(5, ['*'], 'attended_page')
            ->withQueryString();

        return view('dashboard', compact('user', 'events', 'profile', 'wallet', 'orders', 'attendees', 'pastAttended'));
    }
}
