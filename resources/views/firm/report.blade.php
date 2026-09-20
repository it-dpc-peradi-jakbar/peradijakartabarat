<x-layout
    crumb="Law Firm · {{ $firm->name }}"
    title="Laporan ke Admin DPC"
    subtitle="Sampaikan kendala terkait calon yang sudah melamar ke kantor Anda."
    :menu="\App\Support\SidebarMenu::firm('report')"
    :user-meta="'Advokat Pendamping · '.$firm->name"
>
    @push('styles')
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/trix/1.3.1/trix.min.css">
    @endpush

    <x-card class="p-6">
        <form method="POST" action="{{ route('firm.report.store') }}" class="flex flex-col gap-4">
            @csrf
            <div>
                <x-input-label for="candidate_advocate_id" value="Calon advokat" />
                <select id="candidate_advocate_id" name="candidate_advocate_id" required class="app-input mt-1.5">
                    <option value="">Pilih calon</option>
                    @foreach ($candidates as $ca)
                        <option value="{{ $ca->id }}" @selected(old('candidate_advocate_id') == $ca->id)>{{ $ca->user->name }} ({{ $ca->candidate_code }})</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('candidate_advocate_id')" class="mt-2" />
                @if ($candidates->isEmpty())
                    <p class="text-xs text-muted-foreground mt-2">Belum ada pelamar yang dicocokkan ke kantor ini.</p>
                @endif
            </div>
            <div>
                <x-input-label for="body" value="Isi laporan" />
                <input id="body" type="hidden" name="body" value="{{ old('body') }}">
                <trix-editor input="body" class="trix-content mt-1.5 rounded-xl border border-line bg-white px-3 py-2 min-h-[160px]"></trix-editor>
                <x-input-error :messages="$errors->get('body')" class="mt-2" />
            </div>
            <div class="flex justify-end">
                <x-btn type="submit" :disabled="$candidates->isEmpty()">Kirim laporan</x-btn>
            </div>
        </form>
    </x-card>

    @push('scripts')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/trix/1.3.1/trix.min.js"></script>
        <script>document.addEventListener('trix-file-accept', (e) => e.preventDefault());</script>
    @endpush
</x-layout>
