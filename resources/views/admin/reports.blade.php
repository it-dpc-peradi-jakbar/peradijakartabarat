<x-layout
    crumb="Admin DPC Jakarta Barat"
    title="Laporan"
    subtitle="Laporan dari calon advokat dan kantor hukum."
    :menu="\App\Support\SidebarMenu::admin('reports')"
    user-meta="Admin Bidang Magang"
>
    <x-card class="overflow-hidden">
        <div class="p-4 md:p-5 border-b border-line text-sm text-muted-foreground">
            {{ $unread }} belum dibaca
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[640px] text-sm">
                <thead class="bg-muted/60 text-left text-xs uppercase tracking-wide text-muted-foreground">
                    <tr>
                        <th class="px-5 py-3 font-semibold">Pengirim</th>
                        <th class="px-5 py-3 font-semibold">Terkait</th>
                        <th class="px-5 py-3 font-semibold">Waktu</th>
                        <th class="px-5 py-3 font-semibold"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line">
                    @forelse ($reports as $report)
                        <tr>
                            <td class="px-5 py-4">
                                <p class="font-medium">{{ $report->submitter->name }}</p>
                                <p class="text-xs text-muted-foreground mt-0.5">{{ $report->submitter->role === 'law_firm' ? 'Kantor hukum' : 'Calon advokat' }}</p>
                            </td>
                            <td class="px-5 py-4 text-muted-foreground">
                                {{ $report->candidateAdvocate?->user?->name }} · {{ $report->lawFirm?->name }}
                            </td>
                            <td class="px-5 py-4 text-muted-foreground whitespace-nowrap">
                                {{ $report->created_at->translatedFormat('j M Y H:i') }}
                                @if ($report->isUnread())
                                    <x-tag variant="wait" class="ml-2">Baru</x-tag>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-right">
                                <x-btn as="a" href="{{ route('admin.reports.show', $report) }}" variant="ghost">Buka</x-btn>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-10 text-center text-sm text-muted-foreground">Belum ada laporan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>
</x-layout>
