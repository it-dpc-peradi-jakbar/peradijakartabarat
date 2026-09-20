<x-layout
    crumb="Calon Advokat"
    title="Beranda pemagang"
    subtitle="Ringkasan masa magang {{ $ca->internship_months }} bulan, penempatan, dan posisi kamu dalam alur magang."
    :menu="\App\Support\SidebarMenu::candidate('dashboard')"
    :user-meta="$ca->candidate_code.' · Alumni Lulus UPA'"
>
    <x-banners :banners="$banners" />

    <x-card class="p-6">
        <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
            <div>
                <p class="text-[11px] tracking-[0.14em] uppercase text-[#7a7d8b]">Masa magang berjalan</p>
                <p class="mt-1 text-4xl font-serif text-[#0d2a5c]">
                    {{ $ca->bulanBerjalan() }} <span class="text-lg text-[#7a7d8b] font-sans">dari {{ $ca->internship_months }} bulan</span>
                </p>
            </div>
            <div class="text-sm text-right text-[#5b5d68] space-y-1">
                <p>Mulai {{ $ca->internship_started_on?->translatedFormat('j F Y') ?? '—' }}</p>
                <p>Estimasi selesai {{ $ca->estimasiSelesai()?->translatedFormat('j F Y') ?? '—' }}</p>
            </div>
        </div>
        <div class="mt-4 h-2 rounded-full bg-[#eceef2] overflow-hidden">
            <div class="h-full bg-primary rounded-full" style="width: {{ $ca->progresPersen() }}%"></div>
        </div>

        <div class="mt-6 grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-0 md:divide-x border-t border-line pt-5">
            @foreach ($stats as $s)
                <div class="rounded-xl bg-muted/50 md:bg-transparent md:rounded-none px-3 py-3 md:px-4 md:first:pl-0 md:py-0">
                    <p class="text-2xl md:text-3xl font-serif text-navy tabular-nums">{{ $s['value'] }}</p>
                    <p class="text-xs text-muted-foreground mt-1 leading-snug">{{ $s['label'] }}</p>
                </div>
            @endforeach
        </div>
    </x-card>

    <div class="grid grid-cols-1 lg:grid-cols-[minmax(0,1fr)_320px] gap-5 items-start">
        <x-card class="p-6">
            <h2 class="font-semibold text-[15px]">Alur {{ count($timeline) }} langkah magang</h2>
            <p class="text-sm text-[#7a7d8b] mt-1">Posisi kamu saat ini ditandai pada langkah yang sedang berjalan.</p>
            <div class="mt-4 divide-y divide-[#eceef2]">
                @foreach ($timeline as $t)
                    <div class="flex items-center gap-4 py-3.5">
                        @php
                            $done = $t['status'] === 'Selesai';
                            $now = $t['status'] === 'Berjalan';
                        @endphp
                        <div @class([
                            'w-[26px] h-[26px] rounded-full grid place-items-center text-[11.5px] tabular-nums shrink-0',
                            'bg-tag-ok-bg text-tag-ok-fg' => $done,
                            'bg-primary text-white' => $now,
                            'bg-tag-mute-bg text-tag-mute-fg' => ! $done && ! $now,
                        ])>{{ $t['n'] }}</div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium truncate">{{ $t['label'] }}</p>
                            <p class="text-xs text-[#7a7d8b]">{{ $t['actor'] }}</p>
                        </div>
                        <x-tag :variant="$done ? 'ok' : ($now ? 'info' : 'mute')">{{ $t['status'] }}</x-tag>
                    </div>
                @endforeach
            </div>
        </x-card>

        <div class="flex flex-col gap-5">
            <x-card class="p-6">
                <h2 class="font-semibold text-[15px]">Penempatan magang</h2>
                @if ($ca->lawFirm)
                    <p class="mt-3 font-serif text-lg text-[#0d2a5c]">{{ $ca->lawFirm->name }}</p>
                    <p class="text-sm text-[#5b5d68] mt-0.5">{{ $ca->placement_area }}</p>
                    <p class="text-sm text-[#5b5d68]">{{ $ca->lawFirm->address }}</p>
                    @if ($ca->supervisingLawyer)
                        <div class="border-t border-[#eceef2] mt-4 pt-4">
                            <p class="text-xs text-[#7a7d8b]">Advokat pendamping</p>
                            <p class="text-sm font-medium mt-1">{{ $ca->supervisingLawyer->name }}</p>
                            <p class="text-xs text-[#7a7d8b]">Pengalaman praktik {{ $ca->supervisingLawyer->years_of_experience }} tahun · KTA {{ $ca->supervisingLawyer->bar_membership_active ? 'aktif' : 'nonaktif' }}</p>
                        </div>
                    @endif
                @else
                    <p class="text-sm text-[#7a7d8b] mt-3">Belum ditempatkan. Cari lowongan magang untuk mulai mengajukan lamaran.</p>
                @endif
            </x-card>

            <x-navy-callout eyebrow="Dasar hukum" title="Magang wajib minimal 2 tahun terus-menerus">
                Calon Advokat wajib menjalani magang di kantor advokat sekurang-kurangnya 2 tahun terus-menerus.
                <p class="text-xs text-sidebar-dim mt-3">Pasal 3 ayat (1) huruf g, Pasal 29 ayat (5) & (6) UU No. 18/2003</p>
            </x-navy-callout>
        </div>
    </div>
</x-layout>
