@php
    $isEdit = (bool) $banner;
    $expiresValue = old('expires_at', $banner?->expires_at?->format('Y-m-d\TH:i'));
@endphp
<x-layout
    crumb="Admin DPC Jakarta Barat"
    :title="$isEdit ? 'Ubah pengumuman' : 'Buat pengumuman'"
    subtitle="Tampilkan di beranda sasaran yang dipilih. Kosongkan tanggal berakhir jika tidak kedaluwarsa."
    :menu="\App\Support\SidebarMenu::admin('banners')"
    user-meta="Admin Bidang Magang"
>
    @push('styles')
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/trix/1.3.1/trix.min.css">
    @endpush

    <x-card class="p-6">
        <form
            method="POST"
            action="{{ $isEdit ? route('admin.banners.update', $banner) : route('admin.banners.store') }}"
            enctype="multipart/form-data"
            class="flex flex-col gap-4"
        >
            @csrf
            @if ($isEdit)
                @method('PUT')
            @endif

            <div>
                <x-input-label for="title" value="Judul" />
                <input id="title" name="title" type="text" required value="{{ old('title', $banner?->title) }}" class="app-input mt-1.5">
                <x-input-error :messages="$errors->get('title')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="target" value="Sasaran" />
                <select id="target" name="target" required class="app-input mt-1.5">
                    @foreach (\App\Models\Banner::FORM_TARGETS as $value => $label)
                        <option value="{{ $value }}" @selected(old('target', $banner?->target) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('target')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="body" value="Isi" />
                <input id="body" type="hidden" name="body" value="{{ old('body', $banner?->body) }}">
                <trix-editor input="body" class="trix-content mt-1.5 rounded-xl border border-line bg-white px-3 py-2 min-h-[160px]"></trix-editor>
                <x-input-error :messages="$errors->get('body')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="image" value="Gambar (opsional)" />
                @if ($banner?->imageUrl())
                    <img src="{{ $banner->imageUrl() }}" alt="" class="mt-1.5 h-32 w-auto rounded-lg object-cover border border-line">
                    <label class="mt-2 flex items-center gap-2 text-sm cursor-pointer">
                        <input type="checkbox" name="remove_image" value="1" class="rounded-md border-line text-primary">
                        Hapus gambar
                    </label>
                @endif
                <input id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp" class="app-input mt-1.5">
                <p class="text-xs text-muted-foreground mt-1">JPEG, PNG, atau WebP. Maksimal 2 MB.</p>
                <x-input-error :messages="$errors->get('image')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="expires_at" value="Berakhir (opsional)" />
                <input id="expires_at" name="expires_at" type="datetime-local" value="{{ $expiresValue }}" class="app-input mt-1.5">
                <x-input-error :messages="$errors->get('expires_at')" class="mt-2" />
            </div>

            <label class="flex items-center gap-2 text-sm cursor-pointer">
                <input type="hidden" name="published" value="0">
                <input type="checkbox" name="published" value="1" class="rounded-md border-line text-primary" @checked(old('published', $banner && ! $banner->isDraft()))>
                Terbitkan
            </label>

            <div class="flex justify-end gap-2">
                <x-btn as="a" href="{{ route('admin.banners.index') }}" variant="ghost">Batal</x-btn>
                <x-btn type="submit">Simpan</x-btn>
            </div>
        </form>
    </x-card>

    @push('scripts')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/trix/1.3.1/trix.min.js"></script>
        <script>document.addEventListener('trix-file-accept', (e) => e.preventDefault());</script>
    @endpush
</x-layout>
