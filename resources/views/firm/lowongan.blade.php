<x-layout
    crumb="Law Firm · {{ $firm->name }}"
    title="Lowongan magang"
    subtitle="Tandai apakah lowongan ini menyediakan uang transport. Judul dan kuota tidak diubah dari sini."
    :menu="\App\Support\SidebarMenu::firm('lowongan')"
    :user-meta="'Advokat Pendamping · '.$firm->name"
>
    <div class="flex flex-col gap-4">
        @forelse ($jobPostings as $jobPosting)
            <x-card class="p-6">
                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                    <div class="min-w-0">
                        <p class="font-medium">{{ $jobPosting->title }}</p>
                        <p class="text-sm text-muted-foreground mt-1">{{ $jobPosting->description }}</p>
                        <p class="text-xs text-muted-foreground mt-2">Status {{ $jobPosting->status }} · kuota {{ $jobPosting->quota }}</p>
                    </div>
                    <form method="POST" action="{{ route('firm.lowongan.update', $jobPosting) }}" class="shrink-0">
                        @csrf
                        @method('PATCH')
                        <label class="flex items-center gap-2 text-sm cursor-pointer">
                            <input type="hidden" name="provides_transport" value="0">
                            <input
                                type="checkbox"
                                name="provides_transport"
                                value="1"
                                class="rounded-md border-line text-primary"
                                @checked($jobPosting->provides_transport)
                            >
                            Uang transport
                        </label>
                        <x-btn type="submit" variant="ghost" class="mt-3 w-full sm:w-auto justify-center">Simpan</x-btn>
                    </form>
                </div>
            </x-card>
        @empty
            <x-card class="p-8 text-center text-sm text-muted-foreground">Belum ada lowongan untuk kantor ini.</x-card>
        @endforelse
    </div>
</x-layout>
