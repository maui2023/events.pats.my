<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Event;
use App\Models\Profile;
use App\Models\Wallet;
use App\Services\AyuWalletService;

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

        $orders = \App\Models\Order::where('buyer_email', $user->email)->orderByDesc('id')->limit(10)->get();
        $attendees = \App\Models\Attendee::where('email', $user->email)->with('event')->orderByDesc('id')->limit(20)->get();
        return view('dashboard', compact('user', 'events', 'profile', 'wallet', 'orders', 'attendees'));
    }
}
