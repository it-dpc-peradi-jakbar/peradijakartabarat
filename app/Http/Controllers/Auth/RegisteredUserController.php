<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\CandidateAdvocate;
use App\Models\User;
use App\Support\CandidateVerificationChecklist;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register.advocate-candidate');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'national_id_number' => ['nullable', 'string', 'max:32'],
            'university' => ['nullable', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:500'],
        ]);
        $request->validate(\App\Support\WilayahHierarchy::rules());
        if (! \App\Support\WilayahHierarchy::isConsistent(
            $request->input('provinsi_kode'),
            $request->input('kabupaten_kota_kode'),
            $request->input('kecamatan_kode')
        )) {
            throw ValidationException::withMessages([
                'kecamatan_kode' => 'Kombinasi provinsi, kota/kabupaten, dan kecamatan tidak valid.',
            ]);
        }

        $user = DB::transaction(function () use ($request) {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'calon_advokat',
            ]);

            $year = now()->year;
            $sequence = CandidateAdvocate::whereYear('created_at', $year)->count() + 1;

            $candidateAdvocate = CandidateAdvocate::create([
                'user_id' => $user->id,
                'candidate_code' => sprintf('CA-%d-%04d', $year, $sequence),
                'national_id_number' => $request->national_id_number,
                'university' => $request->university,
                'address' => $request->address,
                'provinsi_kode' => $request->provinsi_kode,
                'kabupaten_kota_kode' => $request->kabupaten_kota_kode,
                'kecamatan_kode' => $request->kecamatan_kode,
                'membership_status' => 'ACTIVE',
                'verification_status' => 'PENDING',
            ]);

            CandidateVerificationChecklist::seedFor($candidateAdvocate);
            CandidateVerificationChecklist::syncProfileItem($candidateAdvocate);

            return $user;
        });

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('candidate.verification', absolute: false))
            ->with('status', 'Akun berhasil dibuat. Lengkapi unggahan berkas verifikasi admisi.');
    }
}
