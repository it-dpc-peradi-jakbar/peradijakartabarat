<x-guest-layout>
    <h1 class="font-serif text-xl text-navy mb-1">Registrasi Calon Advokat</h1>
    <p class="text-sm text-muted-foreground mb-6 leading-relaxed">Langkah pertama alur magang wajib: buat akun dan isi data dasar alumni lulus UPA.</p>

    <form method="POST" action="{{ route('register.advocate-candidate') }}" class="space-y-4">
        @csrf

        <div>
            <x-input-label for="name" value="Nama lengkap (dengan gelar)" />
            <x-text-input id="name" class="block mt-1.5 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" placeholder="Contoh: Citra Lestari, S.H." />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="email" value="Alamat email" />
            <x-text-input id="email" class="block mt-1.5 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <x-input-label for="nik" value="NIK" />
                <x-text-input id="nik" class="block mt-1.5 w-full" type="text" name="national_id_number" :value="old('national_id_number')" autocomplete="off" />
                <x-input-error :messages="$errors->get('national_id_number')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="universitas" value="Universitas" />
                <x-text-input id="universitas" class="block mt-1.5 w-full" type="text" name="university" :value="old('university')" autocomplete="off" />
                <x-input-error :messages="$errors->get('university')" class="mt-2" />
            </div>
        </div>

        <div>
            <x-input-label for="address" value="Alamat" />
            <x-text-input id="address" class="block mt-1.5 w-full" type="text" name="address" :value="old('address')" required autocomplete="street-address" placeholder="Contoh: Jl. Panjang No. 12, Kebon Jeruk" />
            <x-input-error :messages="$errors->get('address')" class="mt-2" />
        </div>

        <x-wilayah-selects />

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
                <a class="text-muted-foreground hover:text-ink underline underline-offset-2" href="{{ route('register.law-firm') }}">Daftar kantor hukum</a>
            </div>

            <x-primary-button class="w-full sm:w-auto justify-center">
                Daftar
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
