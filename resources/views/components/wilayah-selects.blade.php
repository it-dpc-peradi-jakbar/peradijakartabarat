@props([
    'provinsi' => null,
    'kabupaten' => null,
    'kecamatan' => null,
    'showKecamatan' => true,
])
<div
    class="flex flex-col gap-4"
    x-data="wilayahSelects({
        provinsi: @js(old('provinsi_kode', $provinsi)),
        kabupaten: @js(old('kabupaten_kota_kode', $kabupaten)),
        kecamatan: @js(old('kecamatan_kode', $kecamatan)),
        url: @js(url('/wilayah')),
    })"
>
    <x-wilayah-combobox
        id="provinsi_kode"
        name="provinsi_kode"
        label="Provinsi"
        placeholder="Cari provinsi"
        kode="provinsi"
        query="qProvinsi"
        items="provinces"
        open-key="provinsi"
        pick="pickProvinsi"
    />

    <div @class(['grid grid-cols-1 gap-4', 'sm:grid-cols-2' => $showKecamatan])>
        <x-wilayah-combobox
            id="kabupaten_kota_kode"
            name="kabupaten_kota_kode"
            label="Kota / Kabupaten"
            placeholder="Cari kota/kabupaten"
            kode="kabupaten"
            query="qKabupaten"
            items="kabupatens"
            open-key="kabupaten"
            pick="pickKabupaten"
            disabled="!provinsi"
        />
        @if ($showKecamatan)
            <x-wilayah-combobox
                id="kecamatan_kode"
                name="kecamatan_kode"
                label="Kecamatan"
                placeholder="Cari kecamatan"
                kode="kecamatan"
                query="qKecamatan"
                items="kecamatans"
                open-key="kecamatan"
                pick="pickKecamatan"
                disabled="!kabupaten"
            />
        @endif
    </div>
</div>
