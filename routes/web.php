<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EventPublicController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Redirect;
use App\Http\Controllers\CreateEventController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RSVPController;
use App\Http\Controllers\CheckinController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');

Route::get('/events/{slug}', [EventPublicController::class, 'show'])->name('events.show');
Route::post('/events/{slug}/rsvp', [RSVPController::class, 'store'])->name('events.rsvp');
Route::post('/events/{slug}/buy', [RSVPController::class, 'purchase'])->name('events.buy');
Route::get('/events/{slug}/join', [RSVPController::class, 'join'])->name('events.join');
Route::get('/events/{slug}/edit', [CreateEventController::class, 'edit'])->name('events.edit');
Route::post('/events/{slug}/edit', [CreateEventController::class, 'update'])->name('events.update');
Route::post('/events/{slug}/staff', [CreateEventController::class, 'addStaff'])->name('events.staff.add');
Route::delete('/events/{slug}/staff/{staffId}', [CreateEventController::class, 'removeStaff'])->name('events.staff.remove');

Route::get('/events/{slug}/qr/{code}', function (string $slug, string $code) {
    $attendee = \App\Models\Attendee::where('qr_code', $code)->firstOrFail();
    $event = \App\Models\Event::where('slug', $slug)->firstOrFail();
    return view('events.qr', compact('event', 'attendee'));
})->name('events.qr');

Route::get('/events/{slug}/scan', [CheckinController::class, 'scanner'])->name('events.scan');

Route::get('/checkin/{code}', [CheckinController::class, 'show'])->name('checkin.show');
Route::post('/checkin/{code}', [CheckinController::class, 'scan'])->name('checkin.scan');
Route::get('/discover', [EventPublicController::class, 'index'])->name('events.discover');

Route::get('/create', [CreateEventController::class, 'index'])->name('events.create');
Route::post('/create', [CreateEventController::class, 'store'])->name('events.store');

Route::view('/pricing', 'pricing')->name('pricing');
Route::post('/pricing/pro/pay', [ProfileController::class, 'upgradeProPay'])->name('pricing.pro.pay');
Route::match(['get', 'post'], '/pricing/pro/return', [ProfileController::class, 'upgradeProReturn'])->name('pricing.pro.return');
Route::post('/subscriptions/securepay/callback', [ProfileController::class, 'upgradeProCallback'])->name('pricing.pro.callback');

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
Route::post('/profile/wallet-label', [ProfileController::class, 'setWalletLabel'])->name('profile.wallet.set');
Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
Route::post('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
Route::post('/profile/organization/create', [ProfileController::class, 'createOrganization'])->name('profile.organization.create');
Route::post('/profile/organization/join', [ProfileController::class, 'joinOrganization'])->name('profile.organization.join');

Route::get('/organizations/manage', [ProfileController::class, 'manageOrganizations'])->name('organizations.manage');
Route::post('/organizations/{organization}/members/{memberId}/approve', [ProfileController::class, 'approveMembership'])->name('organizations.members.approve');
Route::post('/organizations/{organization}/members/{memberId}/reject', [ProfileController::class, 'rejectMembership'])->name('organizations.members.reject');

Route::get('/lang/{locale}', function (string $locale) {
    if (!in_array($locale, ['ms', 'en'])) {
        $locale = 'ms';
    }
    session(['app_locale' => $locale]);
    return Redirect::back();
})->name('lang.switch');
Route::get('/orders/{order}/checkout', [RSVPController::class, 'checkout'])->name('orders.checkout');
Route::get('/orders/{order}/checkout', [RSVPController::class, 'checkout'])->name('orders.checkout');
Route::match(['get', 'post'], '/orders/{order}/pay', [RSVPController::class, 'pay'])->name('orders.pay');
Route::get('/payments/toyyib/return/{order}', [RSVPController::class, 'toyyibReturn'])->name('payments.toyyib.return');
Route::post('/payments/toyyib/callback/{order}', [RSVPController::class, 'toyyibCallback'])->name('payments.toyyib.callback');

// SecurePay Routes
Route::get('/payments/securepay/return/{order}', [RSVPController::class, 'securepayReturn'])->name('payments.securepay.return');
Route::post('/payments/securepay/callback/{order}', [RSVPController::class, 'securepayCallback'])->name('payments.securepay.callback');
Route::get('/orders/{order}/qr', [RSVPController::class, 'downloadQr'])->name('orders.qr.download');
Route::post('/orders/{order}/cancel', [RSVPController::class, 'cancel'])->name('orders.cancel');
