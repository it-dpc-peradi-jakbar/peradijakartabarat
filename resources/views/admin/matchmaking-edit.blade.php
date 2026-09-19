<x-layout
    crumb="Admin DPC Jakarta Barat"
    title="Atur pencocokan"
    subtitle="Pilih satu atau lebih kantor hukum terverifikasi untuk {{ $ca->user->name }}."
    :menu="\App\Support\SidebarMenu::admin('matchmaking')"
    user-meta="Admin Bidang Magang"
>
    <x-card class="p-6 flex flex-col gap-5">
        <div>
            <p class="font-medium">{{ $ca->user->name }}</p>
            <p class="text-sm text-muted-foreground mt-0.5">{{ $ca->candidate_code }} · {{ $ca->wilayahLabel() }}</p>
        </div>

        <form method="POST" action="{{ route('admin.matchmaking.update', $ca) }}" class="flex flex-col gap-4"
            x-data="firmPicker({
                provinsi: @js($ca->provinsi_kode),
                kota: @js($ca->kabupaten_kota_kode),
                kecamatan: @js($ca->kecamatan_kode),
            })"
        >
            @csrf
            @method('PUT')

            <div class="flex flex-col gap-3">
                <input
                    type="search"
                    x-model="q"
                    placeholder="Cari nama atau wilayah kantor"
                    class="app-input"
                    autocomplete="off"
                >
                <nav class="app-tab-bar" aria-label="Filter kantor">
                    <button type="button" @click="area = 'all'" :class="area === 'all' ? 'app-tab-active' : 'app-tab-idle'">Semua</button>
                    <button type="button" @click="area = 'same_provinsi'" :class="area === 'same_provinsi' ? 'app-tab-active' : 'app-tab-idle'">Provinsi sama</button>
                    <button type="button" @click="area = 'same_kota'" :class="area === 'same_kota' ? 'app-tab-active' : 'app-tab-idle'">Kota sama</button>
                    <button type="button" @click="area = 'same_kecamatan'" :class="area === 'same_kecamatan' ? 'app-tab-active' : 'app-tab-idle'">Kecamatan sama</button>
                </nav>
                <p class="text-xs text-muted-foreground">Kantor yang sudah dicentang tetap tampil (supaya bisa di-uncheck). Sisanya mengikuti filter.</p>
            </div>

            <fieldset class="flex flex-col gap-3 max-h-[min(60vh,32rem)] overflow-y-auto pr-1">
                <legend class="text-sm font-medium mb-1">Kantor hukum terverifikasi ({{ $firms->count() }})</legend>
                @forelse ($firms as $firm)
                    @php
                        $wilayah = \App\Support\WilayahHierarchy::label(
                            $firm->provinsi_kode,
                            $firm->kabupaten_kota_kode,
                            $firm->kecamatan_kode,
                            $wilayahNames
                        );
                    @endphp
                    <label
                        class="flex items-start gap-3 rounded-xl border border-line px-4 py-3 cursor-pointer hover:bg-muted/50"
                        x-show="matches($el)"
                        data-name="{{ $firm->name }}"
                        data-wilayah="{{ $wilayah }}"
                        data-provinsi="{{ $firm->provinsi_kode }}"
                        data-kota="{{ $firm->kabupaten_kota_kode }}"
                        data-kecamatan="{{ $firm->kecamatan_kode }}"
                    >
                        <input
                            type="checkbox"
                            name="law_firm_ids[]"
                            value="{{ $firm->id }}"
                            class="mt-1 rounded-md border-line text-primary"
                            @checked(in_array($firm->id, $selectedIds, true))
                            @change="rev++"
                        >
                        <span class="min-w-0">
                            <span class="block font-medium">{{ $firm->name }}</span>
                            <span class="block text-xs text-muted-foreground mt-0.5">{{ $wilayah }}</span>
                        </span>
                    </label>
                @empty
                    <p class="text-sm text-muted-foreground">Belum ada kantor hukum terverifikasi.</p>
                @endforelse
            </fieldset>

            <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2">
                <x-btn as="a" href="{{ route('admin.matchmaking') }}" variant="ghost" class="justify-center">Batal</x-btn>
                <x-btn type="submit" class="justify-center">Simpan pencocokan</x-btn>
            </div>
        </form>
    </x-card>
</x-layout>
