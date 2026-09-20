<x-layout
    crumb="Admin DPC Jakarta Barat"
    title="Pengumuman"
    subtitle="Banner berita untuk beranda calon advokat atau kantor hukum."
    :menu="\App\Support\SidebarMenu::admin('banners')"
    user-meta="Admin Bidang Magang"
>
    <div class="flex justify-end">
        <x-btn as="a" href="{{ route('admin.banners.create') }}">Buat pengumuman</x-btn>
    </div>

    <x-card class="overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[720px] text-sm">
                <thead class="bg-muted/60 text-left text-xs uppercase tracking-wide text-muted-foreground">
                    <tr>
                        <th class="px-5 py-3 font-semibold">Judul</th>
                        <th class="px-5 py-3 font-semibold">Sasaran</th>
                        <th class="px-5 py-3 font-semibold">Status</th>
                        <th class="px-5 py-3 font-semibold">Berakhir</th>
                        <th class="px-5 py-3 font-semibold"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line">
                    @forelse ($banners as $banner)
                        <tr>
                            <td class="px-5 py-4 font-medium">{{ $banner->title }}</td>
                            <td class="px-5 py-4 text-muted-foreground">{{ \App\Models\Banner::FORM_TARGETS[$banner->target] ?? $banner->target }}</td>
                            <td class="px-5 py-4">
                                @if ($banner->isDraft())
                                    <x-tag variant="mute">Draf</x-tag>
                                @elseif ($banner->isExpired())
                                    <x-tag variant="bad">Kedaluwarsa</x-tag>
                                @else
                                    <x-tag variant="ok">Terbit</x-tag>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-muted-foreground whitespace-nowrap">
                                {{ $banner->expires_at?->translatedFormat('j M Y H:i') ?? '—' }}
                            </td>
                            <td class="px-5 py-4 text-right">
                                <div class="inline-flex overflow-hidden rounded-xl border border-line bg-white">
                                    <a
                                        href="{{ route('admin.banners.edit', $banner) }}"
                                        class="inline-flex h-10 w-10 items-center justify-center text-ink-secondary hover:bg-muted"
                                        title="Ubah"
                                        aria-label="Ubah"
                                    >
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 3.487a1.875 1.875 0 1 1 2.652 2.652L7.5 18.152 3 19.5l1.348-4.5 12.514-11.513z"/>
                                        </svg>
                                    </a>
                                    <form method="POST" action="{{ route('admin.banners.destroy', $banner) }}" class="border-l border-line" onsubmit="return confirm('Hapus pengumuman ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button
                                            type="submit"
                                            class="inline-flex h-10 w-10 items-center justify-center p-0 text-tag-bad-fg hover:bg-tag-bad-bg"
                                            title="Hapus"
                                            aria-label="Hapus"
                                        >
                                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9M4.5 7.5h15m-10.125 0 .375-1.5a1.125 1.125 0 0 1 1.087-.9h5.326c.52 0 .986.354 1.087.9l.375 1.5M6.75 7.5V18.75A1.5 1.5 0 0 0 8.25 20.25h7.5a1.5 1.5 0 0 0 1.5-1.5V7.5"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-sm text-muted-foreground">Belum ada pengumuman.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>
</x-layout>
