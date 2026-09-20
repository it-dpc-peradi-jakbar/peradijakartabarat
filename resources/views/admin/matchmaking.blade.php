<x-layout
    crumb="Admin DPC Jakarta Barat"
    title="Pencocokan magang"
    subtitle="Pasangkan calon terverifikasi dengan lowongan. Calon hanya melihat lowongan yang di-match."
    :menu="\App\Support\SidebarMenu::admin('matchmaking')"
    user-meta="Admin Bidang Magang"
>
    <div class="grid grid-cols-2 md:grid-cols-3 gap-3 md:gap-4">
        @foreach ($stats as $s)
            <x-stat-tile :value="$s['value']" :label="$s['label']" />
        @endforeach
    </div>

    <x-card class="overflow-hidden">
        <div class="p-4 md:p-5 border-b border-line flex flex-col gap-4">
            <nav class="app-tab-bar" aria-label="Filter pencocokan">
                <a href="{{ route('admin.matchmaking', array_filter(['filter' => 'all', 'q' => $keyword ?: null])) }}"
                   @class([$filter === 'all' ? 'app-tab-active' : 'app-tab-idle'])>
                    Semua
                </a>
                <a href="{{ route('admin.matchmaking', array_filter(['filter' => 'unmatched', 'q' => $keyword ?: null])) }}"
                   @class([$filter === 'unmatched' ? 'app-tab-active' : 'app-tab-idle'])>
                    Belum di-match
                </a>
                <a href="{{ route('admin.matchmaking', array_filter(['filter' => 'matched', 'q' => $keyword ?: null])) }}"
                   @class([$filter === 'matched' ? 'app-tab-active' : 'app-tab-idle'])>
                    Sudah di-match
                </a>
            </nav>
            <form method="GET" class="flex flex-col sm:flex-row gap-2">
                <input type="hidden" name="filter" value="{{ $filter }}">
                <input type="search" name="q" value="{{ $keyword }}" placeholder="Cari nama, email, atau kode calon" class="app-input flex-1">
                <x-btn type="submit" variant="ghost" class="shrink-0 justify-center">Cari</x-btn>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[720px] text-sm">
                <thead class="bg-muted/60 text-left text-xs uppercase tracking-wide text-muted-foreground">
                    <tr>
                        <th class="px-5 py-3 font-semibold">Calon advokat</th>
                        <th class="px-5 py-3 font-semibold">Preferensi</th>
                        <th class="px-5 py-3 font-semibold">Lowongan di-match</th>
                        <th class="px-5 py-3 font-semibold"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line">
                    @forelse ($candidates as $ca)
                        <tr class="align-top">
                            <td class="px-5 py-4">
                                <p class="font-medium">{{ $ca->user->name }}</p>
                                <p class="text-xs text-muted-foreground mt-0.5">{{ $ca->candidate_code }}</p>
                            </td>
                            <td class="px-5 py-4 text-muted-foreground text-xs leading-relaxed">
                                <p>{{ $ca->workReferenceLabel() }}</p>
                                <p class="mt-0.5">{{ $ca->wants_transport ? 'Butuh uang transport' : 'Transport tidak wajib' }}</p>
                            </td>
                            <td class="px-5 py-4">
                                @if ($ca->matchedJobPostings->isEmpty())
                                    <x-tag variant="wait">Belum di-match</x-tag>
                                @else
                                    <ul class="flex flex-col gap-1">
                                        @foreach ($ca->matchedJobPostings as $job)
                                            <li class="text-sm">{{ $job->title }} <span class="text-xs text-muted-foreground">· {{ $job->lawFirm->name }}</span></li>
                                        @endforeach
                                    </ul>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-right">
                                <x-btn as="a" href="{{ route('admin.matchmaking.edit', $ca) }}" variant="ghost">Atur</x-btn>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-10 text-center text-sm text-muted-foreground">Tidak ada calon terverifikasi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>
</x-layout>
