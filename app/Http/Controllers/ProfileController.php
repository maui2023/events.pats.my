<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Profile;
use App\Models\Wallet;
use App\Models\Organization;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->guest('/login');
        }

        $user = Auth::user();
        $profile = Profile::firstOrCreate(['user_id' => $user->id]);
        $wallet = Wallet::where('user_id', $user->id)->first();
        $myOrganizations = $user->organizations()->orderBy('name')->get();
        $approvedOrganizations = Organization::where('status', 'approved')->orderBy('name')->get();
        $allOrganizations = Organization::orderBy('name')->get();

        return view('profile', compact('user', 'profile', 'wallet', 'myOrganizations', 'approvedOrganizations', 'allOrganizations'));
    }

    public function setWalletLabel(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->guest('/login');
        }

        $user = Auth::user();
        $data = $request->validate([
            'wallet_id' => ['required', 'string', 'min:3', 'max:64', 'regex:/^[A-Za-z0-9_\-]+$/'],
        ]);

        $wallet = Wallet::where('user_id', $user->id)->first();
        if ($wallet && $wallet->wallet_id) {
            return back()->withErrors(['wallet_id' => 'Wallet Label telah ditetapkan dan tidak boleh diubah.']);
        }

        if (!$wallet) {
            $wallet = new Wallet(['user_id' => $user->id]);
        }

        $wallet->wallet_id = $data['wallet_id'];

        try {
            $svc = new \App\Services\AyuWalletService();
            $address = $svc->getNewAddress($wallet->wallet_id);
            if (empty($address)) {
                return back()->withErrors(['wallet_id' => 'Tidak dapat menjana alamat wallet. Sila cuba lagi.']);
            }
            $wallet->wallet_address = $address;
            $wallet->credit_balance = $svc->getBalance($wallet->wallet_id);
            $wallet->save();
        } catch (\Throwable $e) {
            return back()->withErrors(['wallet_id' => 'Ralat sambungan RPC: '.$e->getMessage()]);
        }

        return redirect()->route('profile.show')->with('status', 'Wallet Label ditetapkan dan alamat dijana.');
    }

    public function update(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->guest('/login');
        }

        $user = Auth::user();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'nickname' => ['nullable', 'string', 'max:50'],
            'phone' => ['nullable', 'string', 'max:32'],
            'company' => ['nullable', 'string', 'max:100'],
            'position' => ['nullable', 'string', 'max:100'],
            'avatar' => ['nullable', 'image', 'max:2048'],
        ]);

        $profile = Profile::firstOrCreate(['user_id' => $user->id]);
        $profile->fill(collect($data)->except(['name'])->all());
        $user->name = $data['name'];
        $user->save();

        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $filename = 'avatar_'.$user->id.'_'.time().'.'.$file->getClientOriginalExtension();
            $dir = public_path('uploads/avatars');
            if (!is_dir($dir)) {
                @mkdir($dir, 0755, true);
            }
            $file->move($dir, $filename);
            $profile->avatar = 'uploads/avatars/'.$filename;
        }

        $profile->save();

        return redirect()->route('profile.show')->with('status', 'Profil dikemaskini.');
    }

    public function updatePassword(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->guest('/login');
        }

        $user = Auth::user();
        $data = $request->validate([
            'current_password' => ['required'],
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (!Hash::check($data['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'Kata laluan semasa tidak tepat.']);
        }

        $user->password = Hash::make($data['new_password']);
        $user->save();

        return redirect()->route('profile.show')->with('status', 'Kata laluan berjaya dikemaskini.');
    }

    public function createOrganization(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->guest('/login');
        }
        $user = Auth::user();
        $profile = Profile::firstOrCreate(['user_id' => $user->id]);
        if (!in_array($profile->tier, ['PRO', 'VIP'])) {
            return back()->withErrors(['organization' => 'Hanya PRO/VIP boleh cipta organisasi.']);
        }
        $data = $request->validate(['name' => ['required','string','max:120']]);
        $org = Organization::create([
            'name' => $data['name'],
            'status' => 'pending',
            'created_by' => $user->id,
        ]);
        $org->users()->attach($user->id, ['role' => 'owner', 'status' => 'approved']);
        return redirect()->route('profile.show')->with('status', 'Organisasi dicipta dan menunggu kelulusan admin.');
    }

    public function joinOrganization(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->guest('/login');
        }
        $user = Auth::user();
        $profile = Profile::firstOrCreate(['user_id' => $user->id]);
        if (!in_array($profile->tier, ['PRO', 'VIP'])) {
            return back()->withErrors(['organization' => 'Hanya PRO/VIP boleh menyertai organisasi.']);
        }
        $data = $request->validate(['organization_id' => ['required','integer','exists:organizations,id']]);
        $org = Organization::find($data['organization_id']);
        $org->users()->syncWithoutDetaching([$user->id => ['role' => 'member', 'status' => 'pending']]);
        return redirect()->route('profile.show')->with('status', 'Permohonan menyertai organisasi dihantar. Menunggu kelulusan pencipta.');
    }

    public function manageOrganizations()
    {
        if (!Auth::check()) {
            return redirect()->guest('/login');
        }
        $user = Auth::user();
        $owned = Organization::where('created_by', $user->id)->orderBy('name')->get();
        $asOwner = $user->organizations()->wherePivot('role', 'owner')->get();
        $organizations = $owned->merge($asOwner)->unique('id')->values();

        $pendingByOrg = [];
        foreach ($organizations as $org) {
            $pendingByOrg[$org->id] = $org->users()->wherePivot('status', 'pending')->get();
        }

        return view('organizations.manage', compact('organizations', 'pendingByOrg'));
    }

    public function approveMembership(Request $request, Organization $organization, int $memberId)
    {
        if (!Auth::check()) {
            return redirect()->guest('/login');
        }
        $user = Auth::user();
        $isOwner = $organization->created_by === $user->id || $organization->users()->wherePivot('role', 'owner')->where('users.id', $user->id)->exists();
        if (!$isOwner) {
            return back()->withErrors(['organization' => 'Anda tidak mempunyai kebenaran.']);
        }
        $organization->users()->updateExistingPivot($memberId, ['status' => 'approved']);
        return back()->with('status', 'Permohonan diluluskan.');
    }

    public function rejectMembership(Request $request, Organization $organization, int $memberId)
    {
        if (!Auth::check()) {
            return redirect()->guest('/login');
        }
        $user = Auth::user();
        $isOwner = $organization->created_by === $user->id || $organization->users()->wherePivot('role', 'owner')->where('users.id', $user->id)->exists();
        if (!$isOwner) {
            return back()->withErrors(['organization' => 'Anda tidak mempunyai kebenaran.']);
        }
        $organization->users()->detach($memberId);
        return back()->with('status', 'Permohonan ditolak.');
    }
}
