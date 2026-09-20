<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\LawFirm;
use App\Models\SupervisingLawyer;
use App\Models\User;
use App\Models\VerificationChecklist;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredLawFirmController extends Controller
{
    public function create(): View
    {
        return view('auth.register.law-firm');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'firm_name' => ['required', 'string', 'max:255'],
            'firm_address' => ['required', 'string', 'max:500'],
            'ministry_registration_number' => ['nullable', 'string', 'max:64'],
            'is_equivalent_law_firm' => ['sometimes', 'boolean'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'bar_membership_number' => ['required', 'string', 'max:64'],
            'years_of_experience' => ['nullable', 'integer', 'min:0', 'max:60'],
        ]);
        $validated = array_merge($validated, $request->validate(\App\Support\WilayahHierarchy::rules()));
        if (! \App\Support\WilayahHierarchy::isConsistent(
            $validated['provinsi_kode'],
            $validated['kabupaten_kota_kode'],
            $validated['kecamatan_kode']
        )) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'kecamatan_kode' => 'Kombinasi provinsi, kota/kabupaten, dan kecamatan tidak valid.',
            ]);
        }

        $user = DB::transaction(function () use ($validated) {
            $firm = LawFirm::create([
                'name' => $validated['firm_name'],
                'address' => $validated['firm_address'],
                'provinsi_kode' => $validated['provinsi_kode'],
                'kabupaten_kota_kode' => $validated['kabupaten_kota_kode'],
                'kecamatan_kode' => $validated['kecamatan_kode'],
                'ministry_registration_number' => $validated['ministry_registration_number'] ?? null,
                'is_equivalent_law_firm' => (bool) ($validated['is_equivalent_law_firm'] ?? false),
                'max_quota' => 10,
                'verification_status' => 'PENDING',
            ]);

            $now = now();
            VerificationChecklist::insert([
                [
                    'checkable_type' => LawFirm::class,
                    'checkable_id' => $firm->id,
                    'label' => 'Domisili kantor di wilayah DPC Jakbar',
                    'is_checked' => false,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'checkable_type' => LawFirm::class,
                    'checkable_id' => $firm->id,
                    'label' => 'KTA pendamping aktif',
                    'is_checked' => false,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'checkable_type' => LawFirm::class,
                    'checkable_id' => $firm->id,
                    'label' => 'Bukti pengalaman praktik dilampirkan',
                    'is_checked' => false,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ]);

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => 'law_firm',
            ]);

            SupervisingLawyer::create([
                'law_firm_id' => $firm->id,
                'user_id' => $user->id,
                'name' => $validated['name'],
                'bar_membership_number' => $validated['bar_membership_number'],
                'bar_membership_active' => true,
                'years_of_experience' => (int) ($validated['years_of_experience'] ?? 0),
            ]);

            return $user;
        });

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false))
            ->with('status', 'Registrasi kantor berhasil. Menunggu verifikasi Admin DPC.');
    }
}
