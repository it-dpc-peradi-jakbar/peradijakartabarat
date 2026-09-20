<x-layout
    crumb="Law Firm · {{ $firm->name }}"
    title="Lowongan magang"
    subtitle="Ubah judul, kuota, status, dan uang transport pada lowongan kantor Anda."
    :menu="\App\Support\SidebarMenu::firm('lowongan')"
    :user-meta="'Advokat Pendamping · '.$firm->name"
>
    <div class="flex flex-col gap-4">
        @forelse ($jobPostings as $jobPosting)
            <x-card class="p-6">
                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                    <div class="min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <p class="font-medium">{{ $jobPosting->title }}</p>
                            <x-tag :variant="$jobPosting->status === 'ACTIVE' ? 'ok' : 'mute'">{{ $jobPosting->status === 'ACTIVE' ? 'Aktif' : 'Nonaktif' }}</x-tag>
                            @if ($jobPosting->provides_transport)
                                <x-tag variant="info">Uang transport</x-tag>
                            @endif
                        </div>
                        @if ($jobPosting->description)
                            <p class="text-sm text-muted-foreground mt-1">{{ $jobPosting->description }}</p>
                        @endif
                        <p class="text-xs text-muted-foreground mt-2">{{ $jobPosting->kabupatenLabel() }} · kuota {{ $jobPosting->quota }} · sisa {{ $jobPosting->slotTersisa() }}</p>
                        @if ($jobPosting->practice_areas)
                            <div class="flex gap-2 mt-3 flex-wrap">
                                @foreach ($jobPosting->practice_areas as $bidang)
                                    <x-tag variant="mute">{{ $bidang }}</x-tag>
                                @endforeach
                            </div>
                        @endif
                    </div>
                    <x-btn as="a" href="{{ route('firm.lowongan.edit', $jobPosting) }}" variant="ghost" class="shrink-0 justify-center">Ubah</x-btn>
                </div>
            </x-card>
        @empty
            <x-card class="p-8 text-center text-sm text-muted-foreground">Belum ada lowongan untuk kantor ini.</x-card>
        @endforelse
    </div>
</x-layout>
