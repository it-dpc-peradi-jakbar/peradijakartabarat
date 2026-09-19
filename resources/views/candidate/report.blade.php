<x-layout
    crumb="Calon Advokat"
    title="Laporan ke Admin DPC"
    subtitle="Sampaikan kendala terkait kantor hukum yang sudah dicocokkan untuk Anda."
    :menu="\App\Support\SidebarMenu::candidate('report')"
    :user-meta="$ca->candidate_code.' · Alumni Lulus UPA'"
>
    @push('styles')
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/trix/1.3.1/trix.min.css">
    @endpush

    <x-card class="p-6">
        <form method="POST" action="{{ route('candidate.report.store') }}" class="flex flex-col gap-4">
            @csrf
            <div>
                <x-input-label for="law_firm_id" value="Kantor hukum" />
                <select id="law_firm_id" name="law_firm_id" required class="app-input mt-1.5">
                    <option value="">Pilih kantor</option>
                    @foreach ($firms as $firm)
                        <option value="{{ $firm->id }}" @selected(old('law_firm_id') == $firm->id)>{{ $firm->name }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('law_firm_id')" class="mt-2" />
                @if ($firms->isEmpty())
                    <p class="text-xs text-muted-foreground mt-2">Belum ada kantor yang dicocokkan Admin DPC.</p>
                @endif
            </div>
            <div>
                <x-input-label for="body" value="Isi laporan" />
                <input id="body" type="hidden" name="body" value="{{ old('body') }}">
                <trix-editor input="body" class="trix-content mt-1.5 rounded-xl border border-line bg-white px-3 py-2 min-h-[160px]"></trix-editor>
                <x-input-error :messages="$errors->get('body')" class="mt-2" />
            </div>
            <div class="flex justify-end">
                <x-btn type="submit" :disabled="$firms->isEmpty()">Kirim laporan</x-btn>
            </div>
        </form>
    </x-card>

    @push('scripts')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/trix/1.3.1/trix.min.js"></script>
        <script>document.addEventListener('trix-file-accept', (e) => e.preventDefault());</script>
    @endpush
</x-layout>
