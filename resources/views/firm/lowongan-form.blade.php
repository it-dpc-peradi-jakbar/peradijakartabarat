@php
    $practiceValue = old('practice_areas', implode(', ', $jobPosting->practice_areas ?? []));
@endphp
<x-layout
    crumb="Law Firm · {{ $firm->name }}"
    title="Ubah lowongan"
    subtitle="Lowongan Aktif tampil di Cari Lowongan untuk calon yang dicocokkan ke kantor ini."
    :menu="\App\Support\SidebarMenu::firm('lowongan')"
    :user-meta="'Advokat Pendamping · '.$firm->name"
>
    <x-card class="p-6">
        <form method="POST" action="{{ route('firm.lowongan.update', $jobPosting) }}" class="flex flex-col gap-4">
            @csrf
            @method('PATCH')

            <div>
                <x-input-label for="title" value="Judul" />
                <input id="title" name="title" type="text" required value="{{ old('title', $jobPosting->title) }}" class="app-input mt-1.5">
                <x-input-error :messages="$errors->get('title')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="description" value="Deskripsi (opsional)" />
                <textarea id="description" name="description" rows="4" class="app-input mt-1.5">{{ old('description', $jobPosting->description) }}</textarea>
                <x-input-error :messages="$errors->get('description')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="practice_areas" value="Bidang (opsional)" />
                <input id="practice_areas" name="practice_areas" type="text" value="{{ $practiceValue }}" class="app-input mt-1.5" placeholder="Litigasi, Korporasi, Full-time">
                <p class="text-xs text-muted-foreground mt-1">Pisahkan dengan koma. Filter calon memakai Litigasi, Korporasi, atau Prodeo.</p>
                <x-input-error :messages="$errors->get('practice_areas')" class="mt-2" />
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <x-input-label for="quota" value="Kuota" />
                    <input id="quota" name="quota" type="number" min="1" max="100" required value="{{ old('quota', $jobPosting->quota) }}" class="app-input mt-1.5">
                    <x-input-error :messages="$errors->get('quota')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="status" value="Status" />
                    <select id="status" name="status" required class="app-input mt-1.5">
                        @foreach (['ACTIVE' => 'Aktif', 'INACTIVE' => 'Nonaktif'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('status', $jobPosting->status) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('status')" class="mt-2" />
                </div>
            </div>

            <div>
                <x-input-label value="Area kerja (kota/kabupaten)" />
                <div class="mt-1.5">
                    <x-wilayah-selects
                        :provinsi="old('provinsi_kode', $jobPosting->kabupaten_kota_kode ? substr($jobPosting->kabupaten_kota_kode, 0, 2) : $firm->provinsi_kode)"
                        :kabupaten="old('kabupaten_kota_kode', $jobPosting->kabupaten_kota_kode ?? $firm->kabupaten_kota_kode)"
                        :show-kecamatan="false"
                    />
                </div>
                <p class="text-xs text-muted-foreground mt-1">Preferensi area lowongan untuk pencocokan Admin DPC. Boleh berbeda dari alamat kantor.</p>
            </div>

            <label class="flex items-center gap-2 text-sm cursor-pointer">
                <input type="hidden" name="provides_transport" value="0">
                <input type="checkbox" name="provides_transport" value="1" class="rounded-md border-line text-primary" @checked(old('provides_transport', $jobPosting->provides_transport))>
                Uang transport
            </label>

            <div class="flex justify-end gap-2">
                <x-btn as="a" href="{{ route('firm.lowongan') }}" variant="ghost">Batal</x-btn>
                <x-btn type="submit">Simpan</x-btn>
            </div>
        </form>
    </x-card>
</x-layout>
