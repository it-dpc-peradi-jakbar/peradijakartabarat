<x-layout
    crumb="Calon Advokat"
    title="Cari lowongan magang"
    subtitle="Lowongan hanya dari yang sudah dicocokkan Admin DPC untuk Anda."
    :menu="\App\Support\SidebarMenu::candidate('lowongan')"
    :user-meta="Auth::user()->candidateAdvocate->candidate_code.' · Alumni Lulus UPA'"
>
    @if (! $canApplyForInternship)
        <div class="rounded-xl border border-line bg-muted px-4 py-3 text-sm text-ink-secondary leading-relaxed" role="status">
            Lamaran magang baru bisa diajukan setelah verifikasi admisi disetujui Admin DPC.
            <a href="{{ route('candidate.verification') }}" class="text-primary font-medium underline underline-offset-2">Buka Verifikasi Admisi</a>
        </div>
    @endif

    @if ($errors->has('lamaran'))
        <div class="rounded-xl border border-[#e7cfc7] bg-tag-bad-bg px-4 py-3 text-sm text-tag-bad-fg" role="alert">
            {{ $errors->first('lamaran') }}
        </div>
    @endif

    @if ($awaitingMatch)
        <x-card class="p-8 text-center text-sm text-[#7a7d8b] leading-relaxed">
            Menunggu pencocokan Admin DPC. Lowongan magang akan tampil setelah Sekretariat memasangkan Anda dengan kantor hukum.
        </x-card>
    @else
    <form method="GET" class="flex flex-col gap-3">
        <input
            type="text" name="q" value="{{ $keyword }}" placeholder="Cari kantor hukum atau kata kunci"
            class="app-input flex-1"
        >
        <div class="flex gap-2 overflow-x-auto pb-1 -mx-1 px-1 snap-x snap-mandatory">
            @foreach ($bidangFilter as $label)
                <button
                    type="submit" name="bidang" value="{{ $label }}"
                    class="snap-start rounded-xl min-h-[40px] px-3.5 py-2 text-[13px] whitespace-nowrap border shrink-0 transition
                        {{ $filter === $label ? 'border-primary bg-tag-info-bg text-primary font-medium shadow-sm' : 'border-line bg-white text-ink-secondary hover:bg-muted' }}"
                >{{ $label }}</button>
            @endforeach
        </div>
    </form>

    <div class="flex flex-col gap-4">
        @forelse ($jobPostings as $jobPosting)
            @php $sudahMelamar = in_array($jobPosting->law_firm_id, $internshipApplicationFirmIds); @endphp
            <x-card class="p-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <p class="font-serif text-lg text-[#0d2a5c]">{{ $jobPosting->lawFirm->name }}</p>
                        <x-tag variant="ok">Terverifikasi DPC</x-tag>
                        @if ($jobPosting->provides_transport)
                            <x-tag variant="info">Uang transport</x-tag>
                        @endif
                    </div>
                    <p class="text-sm font-medium mt-1">{{ $jobPosting->title }}</p>
                    <p class="text-sm text-[#5b5d68] mt-1">{{ $jobPosting->description }}</p>
                    <div class="flex gap-2 mt-3 flex-wrap">
                        @foreach ($jobPosting->practice_areas ?? [] as $b)
                            <x-tag variant="mute">{{ $b }}</x-tag>
                        @endforeach
                    </div>
                </div>
                <div class="text-right shrink-0">
                    <p class="text-sm text-[#5b5d68]">Sisa slot <span class="font-semibold text-[#191a20]">{{ $jobPosting->slotTersisa() }}</span></p>
                    <p class="text-xs text-[#7a7d8b] mb-3">dari kuota {{ $jobPosting->quota }}</p>
                    <form method="POST" action="{{ route('candidate.lowongan.lamar', $jobPosting) }}">
                        @csrf
                        <x-btn
                            :variant="$sudahMelamar ? 'done' : 'primary'"
                            :disabled="$sudahMelamar || ! $canApplyForInternship"
                            :title="! $canApplyForInternship && ! $sudahMelamar ? 'Menunggu persetujuan verifikasi admisi Admin DPC' : null"
                        >
                            {{ $sudahMelamar ? 'Lamaran terkirim' : 'Ajukan lamaran' }}
                        </x-btn>
                    </form>
                </div>
            </x-card>
        @empty
            <x-card class="p-8 text-center text-sm text-[#7a7d8b]">Tidak ada lowongan yang cocok dengan pencarianmu.</x-card>
        @endforelse
    </div>
    @endif
</x-layout>
