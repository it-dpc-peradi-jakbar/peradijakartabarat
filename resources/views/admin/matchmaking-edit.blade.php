<x-layout
    crumb="Admin DPC Jakarta Barat"
    title="Atur pencocokan"
    subtitle="Pilih satu atau lebih lowongan aktif. Preferensi calon adalah petunjuk — lowongan di luar referensi tetap bisa dicentang."
    :menu="\App\Support\SidebarMenu::admin('matchmaking')"
    user-meta="Admin Bidang Magang"
>
    <x-card class="p-6 flex flex-col gap-5">
        <div>
            <p class="font-medium">{{ $ca->user->name }}</p>
            <p class="text-sm text-muted-foreground mt-0.5">{{ $ca->candidate_code }} · {{ $ca->wilayahLabel() }}</p>
            <p class="text-sm text-muted-foreground mt-1">Area: {{ $ca->workReferenceLabel() }} · {{ $ca->wants_transport ? 'Butuh uang transport' : 'Transport tidak wajib' }}</p>
        </div>

        <form method="POST" action="{{ route('admin.matchmaking.update', $ca) }}" class="flex flex-col gap-4"
            x-data="jobPicker()"
        >
            @csrf
            @method('PUT')

            <div class="flex flex-col gap-3">
                <input
                    type="search"
                    x-model="q"
                    placeholder="Cari judul, kantor, atau wilayah lowongan"
                    class="app-input"
                    autocomplete="off"
                >
                <nav class="app-tab-bar" aria-label="Filter lowongan">
                    <button type="button" @click="area = 'all'" :class="area === 'all' ? 'app-tab-active' : 'app-tab-idle'">Semua</button>
                    <button type="button" @click="area = 'fit'" :class="area === 'fit' ? 'app-tab-active' : 'app-tab-idle'">Cocok preferensi</button>
                    <button type="button" @click="area = 'outside'" :class="area === 'outside' ? 'app-tab-active' : 'app-tab-idle'">Di luar preferensi</button>
                </nav>
                <p class="text-xs text-muted-foreground">Lowongan yang sudah dicentang tetap tampil. Menyimpan di luar preferensi diperbolehkan.</p>
            </div>

            <fieldset class="flex flex-col gap-3 max-h-[min(60vh,32rem)] overflow-y-auto pr-1">
                <legend class="text-sm font-medium mb-1">Lowongan aktif terverifikasi ({{ $jobs->count() }})</legend>
                @forelse ($jobs as $job)
                    @php
                        $fits = $ca->jobFitsPreferences($job);
                        $kota = \App\Support\WilayahHierarchy::label(
                            $job->kabupaten_kota_kode ? substr($job->kabupaten_kota_kode, 0, 2) : null,
                            $job->kabupaten_kota_kode,
                            null,
                            $wilayahNames
                        );
                    @endphp
                    <label
                        class="flex items-start gap-3 rounded-xl border border-line px-4 py-3 cursor-pointer hover:bg-muted/50"
                        x-show="matches($el)"
                        data-name="{{ $job->title }} {{ $job->lawFirm->name }}"
                        data-wilayah="{{ $kota }}"
                        data-fits="{{ $fits ? '1' : '0' }}"
                    >
                        <input
                            type="checkbox"
                            name="job_posting_ids[]"
                            value="{{ $job->id }}"
                            class="mt-1 rounded-md border-line text-primary"
                            @checked(in_array($job->id, $selectedIds, true))
                            @change="rev++"
                        >
                        <span class="min-w-0">
                            <span class="block font-medium">{{ $job->title }}</span>
                            <span class="block text-xs text-muted-foreground mt-0.5">{{ $job->lawFirm->name }} · {{ $kota }}{{ $job->provides_transport ? ' · Uang transport' : '' }}</span>
                            @unless ($fits)
                                <x-tag variant="wait" class="mt-1">Di luar preferensi</x-tag>
                            @endunless
                        </span>
                    </label>
                @empty
                    <p class="text-sm text-muted-foreground">Belum ada lowongan aktif dari kantor terverifikasi.</p>
                @endforelse
            </fieldset>

            <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2">
                <x-btn as="a" href="{{ route('admin.matchmaking') }}" variant="ghost" class="justify-center">Batal</x-btn>
                <x-btn type="submit" class="justify-center">Simpan pencocokan</x-btn>
            </div>
        </form>
    </x-card>
</x-layout>
