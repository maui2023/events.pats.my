<?php

namespace App\Http\Controllers;

use App\Models\Attendee;
use App\Models\Event;
use App\Models\Order;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use App\Services\ToyyibpayService;
use App\Models\Profile;
use Illuminate\Support\Facades\Response;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class RSVPController extends Controller
{
    public function store(Request $request, string $slug)
    {
        $event = Event::query()->where('slug', $slug)->where('is_published', true)->firstOrFail();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email'],
            'ticket_id' => ['required', 'integer'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $ticket = Ticket::query()->where('id', $data['ticket_id'])->where('event_id', $event->id)->firstOrFail();

        if ($ticket->type !== 'free') {
            abort(400);
        }

        $order = Order::create([
            'event_id' => $event->id,
            'ticket_id' => $ticket->id,
            'buyer_name' => $data['name'],
            'buyer_email' => $data['email'],
            'quantity' => $data['quantity'],
            'total_amount' => 0,
            'status' => 'paid',
        ]);

        $attendee = Attendee::create([
            'order_id' => $order->id,
            'event_id' => $event->id,
            'ticket_id' => $ticket->id,
            'name' => $data['name'],
            'email' => $data['email'],
            'qr_code' => Str::uuid()->toString(),
        ]);
        $ticket->increment('sold', (int) $data['quantity']);

        return redirect()->route('events.qr', [$event->slug, $attendee->id]);
    }

    public function purchase(Request $request, string $slug)
    {
        $event = Event::query()->where('slug', $slug)->where('is_published', true)->firstOrFail();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email'],
            'ticket_id' => ['required', 'integer'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $ticket = Ticket::query()->where('id', $data['ticket_id'])->where('event_id', $event->id)->firstOrFail();

        if (!in_array($ticket->type, ['paid', 'sponsor'])) {
            abort(400);
        }

        $total = ((float)$ticket->price) * (int)$data['quantity'];

        $order = Order::create([
            'event_id' => $event->id,
            'ticket_id' => $ticket->id,
            'buyer_name' => $data['name'],
            'buyer_email' => $data['email'],
            'quantity' => $data['quantity'],
            'total_amount' => $total,
            'status' => 'pending',
        ]);

        return redirect()->route('events.show', $event->slug)->with('order_created', ['id' => $order->id]);
    }

    public function join(Request $request, string $slug)
    {
        $event = Event::query()->where('slug', $slug)->where('is_published', true)->firstOrFail();

        if (!Auth::check()) {
            $request->session()->put('url.intended', route('events.join', $slug));
            return redirect()->guest('/login');
        }

        $user = Auth::user();
        $ticket = $event->tickets()->orderBy('price')->first();
        if (!$ticket) {
            return redirect()->route('events.show', $event->slug)->withErrors(['ticket' => 'Tiada tiket tersedia.']);
        }

        if ($ticket->type === 'free') {
            $order = Order::create([
                'event_id' => $event->id,
                'ticket_id' => $ticket->id,
                'buyer_name' => $user->name,
                'buyer_email' => $user->email,
                'quantity' => 1,
                'total_amount' => 0,
                'status' => 'paid',
            ]);

            $attendee = Attendee::create([
                'order_id' => $order->id,
                'event_id' => $event->id,
                'ticket_id' => $ticket->id,
                'name' => $user->name,
                'email' => $user->email,
                'qr_code' => Str::uuid()->toString(),
            ]);
            $ticket->increment('sold', 1);

            return redirect()->route('events.qr', [$event->slug, $attendee->id]);
        }

        $total = ((float)$ticket->price) * 1;
        $order = Order::create([
            'event_id' => $event->id,
            'ticket_id' => $ticket->id,
            'buyer_name' => $user->name,
            'buyer_email' => $user->email,
            'quantity' => 1,
            'total_amount' => $total,
            'status' => 'pending',
        ]);

        return redirect()->route('orders.checkout', $order->id);
    }

    public function checkout(Request $request, int $orderId)
    {
        if (!Auth::check()) {
            $request->session()->put('url.intended', route('orders.checkout', $orderId));
            return redirect()->guest('/login');
        }
        $order = Order::findOrFail($orderId);
        $event = Event::findOrFail($order->event_id);
        $ticket = Ticket::findOrFail($order->ticket_id);
        $attendee = \App\Models\Attendee::where('order_id', $order->id)->first();
        $start = optional($event->start_at);
        $end = optional($event->end_at) ?: ($start ? $start->copy()->addHours(2) : null);
        $fmt = function($dt){ return $dt ? $dt->copy()->timezone('UTC')->format('Ymd\THis\Z') : ''; };
        $text = urlencode($event->title);
        $details = urlencode(strip_tags($event->description ?? ''));
        $location = urlencode($event->location ?? '');
        $calendarUrl = ($start && $end)
            ? "https://calendar.google.com/calendar/render?action=TEMPLATE&text={$text}&dates={$fmt($start)}/{$fmt($end)}&details={$details}&location={$location}"
            : null;
        return view('orders.checkout', compact('order', 'event', 'ticket', 'attendee', 'calendarUrl'));
    }

    public function pay(Request $request, int $orderId)
    {
        if (!Auth::check()) {
            $request->session()->put('url.intended', route('orders.checkout', $orderId));
            return redirect()->guest('/login');
        }
        $order = Order::findOrFail($orderId);
        $event = Event::findOrFail($order->event_id);
        $ticket = Ticket::findOrFail($order->ticket_id);
        $user = Auth::user();
        $profile = Profile::firstOrCreate(['user_id' => $user->id]);

        if ($ticket->type === 'free') {
            $order->status = 'paid';
            $order->total_amount = 0;
            $order->save();
            $attendee = Attendee::where('order_id', $order->id)->first();
            if (!$attendee) {
                $attendee = Attendee::create([
                    'order_id' => $order->id,
                    'event_id' => $order->event_id,
                    'ticket_id' => $order->ticket_id,
                    'name' => $order->buyer_name,
                    'email' => $order->buyer_email,
                    'qr_code' => Str::uuid()->toString(),
                ]);
            }
            $ticket->increment('sold', $order->quantity ?? 1);
            return redirect()->route('events.qr', [$event->slug, $attendee->id]);
        }

        $phone = preg_replace('/[^0-9]/', '', (string) ($profile->phone ?? ''));
        if (empty($phone)) {
            return redirect()->route('profile.show')->withErrors(['phone' => 'Sila lengkapkan nombor telefon di Profil untuk pembayaran Toyyibpay.']);
        }

        $svc = new ToyyibpayService();
        $returnUrl = route('payments.toyyib.return', $order->id);
        $callbackUrl = route('payments.toyyib.callback', $order->id);

        $payload = [
            'userSecretKey' => $svc->secret(),
            'categoryCode' => $svc->categoryCode(),
            'billName' => mb_substr(strip_tags($event->title.' - '.$ticket->name), 0, 30),
            'billDescription' => mb_substr(strip_tags($event->description ?? 'Ticket Purchase'), 0, 100),
            'billPriceSetting' => 0,
            'billPayorInfo' => 1,
            'billAmount' => (int) round(((float) $order->total_amount) * 100),
            'billReturnUrl' => $returnUrl,
            'billCallbackUrl' => $callbackUrl,
            'billExternalReferenceNo' => (string) $order->id,
            'billTo' => $order->buyer_name,
            'billEmail' => $order->buyer_email,
            'billPhone' => $phone,
            'billSplitPayment' => 0,
            'billSplitPaymentArgs' => '',
            'billPaymentChannel' => '0',
            'billContentEmail' => 'Terima kasih atas pembelian tiket.',
            'billChargeToCustomer' => 1,
            'billExpiryDays' => 3,
        ];

        $bill = $svc->createBill($payload);
        $billCode = $bill['BillCode'];
        $payUrl = rtrim($svc->baseUrl(), '/').'/'.$billCode;

        return redirect()->away($payUrl);
    }

    public function toyyibReturn(Request $request, int $orderId)
    {
        $order = Order::findOrFail($orderId);
        $statusId = (int) ($request->query('status_id') ?? 0);
        $billcode = $request->query('billcode');

        if ($statusId === 1) {
            // Terima sebagai berjaya (jika callback belum sampai)
            $order->status = 'paid';
            if (!empty($billcode)) {
                $order->payment_reference = $billcode;
            }
            $order->save();

            $attendee = Attendee::where('order_id', $order->id)->first();
            if (!$attendee) {
                $attendee = Attendee::create([
                    'order_id' => $order->id,
                    'event_id' => $order->event_id,
                    'ticket_id' => $order->ticket_id,
                    'name' => $order->buyer_name,
                    'email' => $order->buyer_email,
                    'qr_code' => Str::uuid()->toString(),
                ]);
            }
            $ticket = Ticket::findOrFail($order->ticket_id);
            $ticket->increment('sold', $order->quantity ?? 1);
            $event = Event::findOrFail($order->event_id);
            return redirect()->route('events.qr', [$event->slug, $attendee->id]);
        }

        // Gagal atau dibatalkan → kembali ke checkout
        return redirect()->route('orders.checkout', $order->id)->withErrors(['payment' => 'Pembayaran gagal atau dibatalkan.']);
    }

    public function toyyibCallback(Request $request, int $orderId)
    {
        $order = Order::findOrFail($orderId);
        $statusId = (int) ($request->input('status_id') ?? 0);
        $billcode = $request->input('billcode');
        $amount = $request->input('amount');
        if ($statusId === 1) {
            $order->status = 'paid';
            $order->payment_reference = $billcode;
            $order->save();
        }
        return response()->json(['success' => true]);
    }

    public function downloadQr(Request $request, int $orderId)
    {
        if (!Auth::check()) {
            $request->session()->put('url.intended', route('orders.qr.download', $orderId));
            return redirect()->guest('/login');
        }
        $order = Order::findOrFail($orderId);
        $attendee = Attendee::where('order_id', $order->id)->firstOrFail();
        $url = route('checkin.show', $attendee->qr_code);
        try {
            $png = QrCode::format('png')->size(360)->margin(1)->generate($url);
            return Response::make($png, 200, [
                'Content-Type' => 'image/png',
                'Content-Disposition' => 'attachment; filename="qr-'.$attendee->id.'.png"'
            ]);
        } catch (\Throwable $e) {
            $svg = QrCode::format('svg')->size(360)->margin(1)->generate($url);
            return Response::make($svg, 200, [
                'Content-Type' => 'image/svg+xml',
                'Content-Disposition' => 'attachment; filename="qr-'.$attendee->id.'.svg"'
            ]);
        }
    }

    public function cancel(Request $request, int $orderId)
    {
        if (!Auth::check()) {
            $request->session()->put('url.intended', route('orders.checkout', $orderId));
            return redirect()->guest('/login');
        }
        $order = Order::findOrFail($orderId);
        $user = Auth::user();
        if (strtolower($order->buyer_email) !== strtolower($user->email)) {
            return back()->withErrors(['order' => 'Anda tidak mempunyai akses ke pesanan ini.']);
        }
        if (strtolower($order->status) !== 'pending') {
            return back()->withErrors(['order' => 'Hanya pesanan berstatus PENDING boleh dibatalkan.']);
        }
        $order->attendees()->delete();
        $order->delete();
        return redirect()->route('dashboard')->with('status', 'Pesanan dibatalkan dan tiket dibuang.');
    }
}
