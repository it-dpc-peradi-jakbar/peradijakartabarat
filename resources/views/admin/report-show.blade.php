<x-layout
    crumb="Admin DPC Jakarta Barat"
    title="Detail laporan"
    subtitle="Dikirim {{ $report->created_at->translatedFormat('j F Y H:i') }}."
    :menu="\App\Support\SidebarMenu::admin('reports')"
    user-meta="Admin Bidang Magang"
>
    <x-card class="p-6 flex flex-col gap-4">
        <div>
            <p class="text-xs uppercase tracking-wide text-muted-foreground">Pengirim</p>
            <p class="font-medium mt-1">{{ $report->submitter->name }}</p>
            <p class="text-sm text-muted-foreground">{{ $report->submitter->role === 'law_firm' ? 'Kantor hukum' : 'Calon advokat' }}</p>
        </div>
        <div>
            <p class="text-xs uppercase tracking-wide text-muted-foreground">Terkait</p>
            <p class="text-sm mt-1">{{ $report->candidateAdvocate?->user?->name }} ({{ $report->candidateAdvocate?->candidate_code }})</p>
            <p class="text-sm text-muted-foreground">{{ $report->lawFirm?->name }}</p>
        </div>
        <div class="border-t border-line pt-4">
            <p class="text-xs uppercase tracking-wide text-muted-foreground mb-2">Isi</p>
            <div class="prose prose-sm max-w-none text-ink">{!! $report->body !!}</div>
        </div>
        <div>
            <x-btn as="a" href="{{ route('admin.reports.index') }}" variant="ghost">Kembali</x-btn>
        </div>
    </x-card>
</x-layout>
