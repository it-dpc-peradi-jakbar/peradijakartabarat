<x-guest-layout>
    <h1 class="font-serif text-xl text-navy mb-1">Registrasi Law Firm</h1>
    <p class="text-sm text-muted-foreground mb-6 leading-relaxed">Daftar kantor hukum dan advokat pendamping. Setelah registrasi, Admin DPC memverifikasi kantor sebelum lowongan magang tampil untuk calon advokat.</p>

    <form method="POST" action="{{ route('register.law-firm') }}" class="space-y-4">
        @csrf

        <div>
            <x-input-label for="firm_name" value="Nama kantor hukum" />
            <x-text-input id="firm_name" class="block mt-1.5 w-full" type="text" name="firm_name" :value="old('firm_name')" required autofocus autocomplete="organization" placeholder="Contoh: Wibisono & Rekan" />
            <x-input-error :messages="$errors->get('firm_name')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="firm_address" value="Alamat kantor (Jakarta Barat)" />
            <x-text-input id="firm_address" class="block mt-1.5 w-full" type="text" name="firm_address" :value="old('firm_address')" required autocomplete="street-address" />
            <x-input-error :messages="$errors->get('firm_address')" class="mt-2" />
        </div>

        <x-wilayah-selects />

        <div>
            <x-input-label for="ministry_registration_number" value="Nomor SK / registrasi Kemenkumham (opsional)" />
            <x-text-input id="ministry_registration_number" class="block mt-1.5 w-full" type="text" name="ministry_registration_number" :value="old('ministry_registration_number')" autocomplete="off" />
            <x-input-error :messages="$errors->get('ministry_registration_number')" class="mt-2" />
        </div>

        <div>
            <label for="is_equivalent_law_firm" class="inline-flex min-h-[44px] items-center gap-2 sm:min-h-0">
                <input id="is_equivalent_law_firm" type="checkbox" class="rounded-md border-line text-primary shadow-sm focus:ring-primary/30" name="is_equivalent_law_firm" value="1" @checked(old('is_equivalent_law_firm'))>
                <span class="text-sm text-ink-secondary">Kantor setara (mis. LBH kampus) sesuai PERADI</span>
            </label>
            <x-input-error :messages="$errors->get('is_equivalent_law_firm')" class="mt-2" />
        </div>

        <hr class="border-line" />

        <p class="text-sm font-medium text-ink">Advokat pendamping (akun login)</p>

        <div>
            <x-input-label for="name" value="Nama lengkap (dengan gelar)" />
            <x-text-input id="name" class="block mt-1.5 w-full" type="text" name="name" :value="old('name')" required autocomplete="name" placeholder="Contoh: Dr. Hendra Wibisono, S.H., M.H." />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="email" value="Email login" />
            <x-text-input id="email" class="block mt-1.5 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <x-input-label for="bar_membership_number" value="Nomor KTA PERADI" />
                <x-text-input id="bar_membership_number" class="block mt-1.5 w-full" type="text" name="bar_membership_number" :value="old('bar_membership_number')" required autocomplete="off" />
                <x-input-error :messages="$errors->get('bar_membership_number')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="years_of_experience" value="Pengalaman praktik (tahun)" />
                <x-text-input id="years_of_experience" class="block mt-1.5 w-full" type="number" name="years_of_experience" min="0" max="60" :value="old('years_of_experience', '0')" />
                <x-input-error :messages="$errors->get('years_of_experience')" class="mt-2" />
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <x-input-label for="password" value="Kata sandi" />
                <x-text-input id="password" class="block mt-1.5 w-full" type="password" name="password" required autocomplete="new-password" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="password_confirmation" value="Konfirmasi kata sandi" />
                <x-text-input id="password_confirmation" class="block mt-1.5 w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
            </div>
        </div>

        <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-between gap-3 pt-2">
            <div class="flex flex-col gap-1 text-sm text-center sm:text-left">
                <a class="text-muted-foreground hover:text-ink underline underline-offset-2" href="{{ route('login') }}">Sudah punya akun?</a>
                <a class="text-muted-foreground hover:text-ink underline underline-offset-2" href="{{ route('register.advocate-candidate') }}">Daftar calon advokat</a>
            </div>

            <x-primary-button class="w-full sm:w-auto justify-center">
                Daftar kantor
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
