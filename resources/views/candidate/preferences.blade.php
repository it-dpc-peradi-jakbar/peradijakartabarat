<x-layout
    crumb="Calon Advokat"
    title="Preferensi magang"
    subtitle="Admin DPC memakai ini sebagai petunjuk pencocokan. Mereka tetap bisa memasangkan lowongan di luar preferensi."
    :menu="\App\Support\SidebarMenu::candidate('preferences')"
    :user-meta="$ca->candidate_code.' · Alumni Lulus UPA'"
>
    <x-card class="p-6">
        <form method="POST" action="{{ route('candidate.preferences.update') }}" class="flex flex-col gap-4">
            @csrf
            @method('PATCH')

            <p class="text-sm text-muted-foreground">Domisili terdaftar: {{ $ca->wilayahLabel() }}</p>

            <label class="flex items-start gap-3 text-sm cursor-pointer">
                <input type="hidden" name="work_reference_global" value="0">
                <input type="checkbox" name="work_reference_global" value="1" class="mt-0.5 rounded-md border-line text-primary" @checked(old('work_reference_global', $ca->wantsAnywhere()))>
                <span>
                    <span class="block font-medium">Bisa magang di kota mana pun</span>
                    <span class="block text-xs text-muted-foreground mt-0.5">Jika tidak dicentang, Admin DPC melihat Anda lebih memilih kota/kabupaten domisili. Pilihan kota spesifik belum dibuka.</span>
                </span>
            </label>

            <label class="flex items-start gap-3 text-sm cursor-pointer">
                <input type="hidden" name="wants_transport" value="0">
                <input type="checkbox" name="wants_transport" value="1" class="mt-0.5 rounded-md border-line text-primary" @checked(old('wants_transport', $ca->wants_transport))>
                <span>
                    <span class="block font-medium">Membutuhkan uang transport</span>
                    <span class="block text-xs text-muted-foreground mt-0.5">Jika tidak dicentang, lowongan tanpa transport tetap cocok sebagai petunjuk.</span>
                </span>
            </label>

            <div class="flex justify-end">
                <x-btn type="submit">Simpan preferensi</x-btn>
            </div>
        </form>
    </x-card>
</x-layout>
