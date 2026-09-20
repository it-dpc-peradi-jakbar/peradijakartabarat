<x-layout
    crumb="Law Firm · {{ $firm->name }}"
    title="Dashboard & kuota bimbingan"
    subtitle="Pantau kuota gabungan kantor dan advokat pendamping, serta progres seluruh pemagang."
    :menu="\App\Support\SidebarMenu::firm('dashboard')"
    :user-meta="'Advokat Pendamping · '.$firm->name"
>
    <div class="grid grid-cols-1 lg:grid-cols-[minmax(0,1fr)_360px] gap-5 items-start">
        <x-card class="p-6">
            <p class="text-[11px] tracking-[0.14em] uppercase text-[#7a7d8b]">Kuota bimbingan gabungan</p>
            <p class="mt-1 text-4xl font-serif text-[#0d2a5c]">
                {{ $kuotaTerpakai }} <span class="text-lg text-[#7a7d8b] font-sans">/ {{ $firm->max_quota }} calon advokat</span>
            </p>
            <div class="mt-4 flex gap-1.5">
                @foreach ($kuotaSlots as $filled)
                    <div class="flex-1 h-[30px] rounded-md {{ $filled ? 'bg-primary' : 'bg-[#edeef2]' }}"></div>
                @endforeach
            </div>
            <p class="text-sm text-[#7a7d8b] mt-4">Hard-cap Peraturan PERADI No. 1/2015: maksimum {{ $firm->max_quota }} calon advokat dalam waktu bersamaan untuk kantor + advokat pendamping.</p>
        </x-card>

        <x-card class="p-6">
            <h2 class="font-semibold text-[15px]">Perlu tindakan</h2>
            <div class="mt-3 divide-y divide-[#eceef2]">
                @forelse ($firmTasks as $t)
                    <div class="flex items-center justify-between gap-3 py-3">
                        <div class="min-w-0">
                            <p class="text-sm font-medium truncate">{{ $t['label'] }}</p>
                            <p class="text-xs text-[#7a7d8b]">{{ $t['sub'] }}</p>
                        </div>
                        <a href="{{ $t['route'] }}" class="text-xs font-medium text-primary border border-[#c9d9f0] rounded-lg px-3 py-1.5 whitespace-nowrap hover:bg-[#eef3fb]">{{ $t['cta'] }}</a>
                    </div>
                @empty
                    <p class="py-6 text-sm text-[#7a7d8b]">Tidak ada tindakan yang perlu dilakukan saat ini.</p>
                @endforelse
            </div>
        </x-card>
    </div>

    <x-card class="overflow-hidden">
        <div class="p-5 md:p-6 border-b border-line">
            <h2 class="app-section-title">Pemagang aktif</h2>
        </div>

        <div class="md:hidden divide-y divide-line">
            @forelse ($pemagang as $row)
                <div class="p-5 space-y-3">
                    <div>
                        <p class="font-medium">{{ $row['ca']->user->name }}</p>
                        <p class="text-xs text-muted-foreground">{{ $row['ca']->candidate_code }}</p>
                    </div>
                    <div class="flex items-center justify-between gap-3 text-sm">
                        <span class="text-muted-foreground">Bidang</span>
                        <span class="text-ink-secondary text-right">{{ $row['ca']->placement_area }}</span>
                    </div>
                    <div>
                        <div class="flex items-center justify-between text-xs text-muted-foreground mb-1.5">
                            <span>Progres magang</span>
                            <span>bulan {{ $row['ca']->bulanBerjalan() }}/{{ $row['ca']->internship_months }}</span>
                        </div>
                        <div class="h-2 rounded-full bg-muted overflow-hidden">
                            <div class="h-full bg-primary rounded-full" style="width: {{ $row['ca']->progresPersen() }}%"></div>
                        </div>
                    </div>
                    @if (config('features.logbook'))
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-muted-foreground">Logbook bulan ini</span>
                        <x-tag :variant="$row['variant']">{{ $row['logStatus'] }}</x-tag>
                    </div>
                    @endif
                </div>
            @empty
                <p class="p-8 text-center text-sm text-muted-foreground">Belum ada pemagang aktif.</p>
            @endforelse
        </div>

        <div class="hidden md:block app-table-wrap">
            <table class="app-table">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Bidang</th>
                        <th>Progres</th>
                        @if (config('features.logbook'))
                        <th>Logbook bulan ini</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pemagang as $row)
                        <tr>
                            <td>
                                <p class="font-medium">{{ $row['ca']->user->name }}</p>
                                <p class="text-xs text-muted-foreground">{{ $row['ca']->candidate_code }}</p>
                            </td>
                            <td class="text-ink-secondary">{{ $row['ca']->placement_area }}</td>
                            <td>
                                <div class="w-32 h-2 rounded-full bg-muted overflow-hidden">
                                    <div class="h-full bg-primary rounded-full" style="width: {{ $row['ca']->progresPersen() }}%"></div>
                                </div>
                                <p class="text-xs text-muted-foreground mt-1">bulan {{ $row['ca']->bulanBerjalan() }}/{{ $row['ca']->internship_months }}</p>
                            </td>
                            @if (config('features.logbook'))
                            <td><x-tag :variant="$row['variant']">{{ $row['logStatus'] }}</x-tag></td>
                            @endif
                        </tr>
                    @empty
                        <tr><td colspan="{{ config('features.logbook') ? 4 : 3 }}" class="py-8 text-center text-muted-foreground">Belum ada pemagang aktif.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>
</x-layout>
