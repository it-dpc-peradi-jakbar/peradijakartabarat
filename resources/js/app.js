import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.data('wilayahSelects', (initial = {}) => ({
    url: initial.url || '/wilayah',
    open: '',
    provinsi: initial.provinsi || '',
    kabupaten: initial.kabupaten || '',
    kecamatan: initial.kecamatan || '',
    qProvinsi: '',
    qKabupaten: '',
    qKecamatan: '',
    provinces: [],
    kabupatens: [],
    kecamatans: [],
    async init() {
        this.provinces = await this.fetchChildren();
        this.qProvinsi = this.nama(this.provinces, this.provinsi);
        if (this.provinsi) {
            this.kabupatens = await this.fetchChildren(this.provinsi);
            this.qKabupaten = this.nama(this.kabupatens, this.kabupaten);
        }
        if (this.kabupaten) {
            this.kecamatans = await this.fetchChildren(this.kabupaten);
            this.qKecamatan = this.nama(this.kecamatans, this.kecamatan);
        }
    },
    nama(list, kode) {
        return (list || []).find((item) => item.kode === kode)?.nama || '';
    },
    filtered(list, query, kode) {
        const needle = (query || '').trim().toLowerCase();
        const selected = this.nama(list, kode).toLowerCase();
        if (! needle || needle === selected) {
            return list || [];
        }

        return (list || []).filter((item) =>
            item.nama.toLowerCase().includes(needle) || item.kode.toLowerCase().includes(needle)
        );
    },
    async fetchChildren(parent) {
        const query = parent ? `?parent=${encodeURIComponent(parent)}` : '';
        const response = await fetch(`${this.url}${query}`, { headers: { Accept: 'application/json' } });
        if (! response.ok) {
            return [];
        }
        return response.json();
    },
    async pickProvinsi(item) {
        this.provinsi = item.kode;
        this.qProvinsi = item.nama;
        this.open = '';
        this.kabupaten = '';
        this.kecamatan = '';
        this.qKabupaten = '';
        this.qKecamatan = '';
        this.kecamatans = [];
        this.kabupatens = await this.fetchChildren(item.kode);
    },
    async pickKabupaten(item) {
        this.kabupaten = item.kode;
        this.qKabupaten = item.nama;
        this.open = '';
        this.kecamatan = '';
        this.qKecamatan = '';
        this.kecamatans = await this.fetchChildren(item.kode);
    },
    pickKecamatan(item) {
        this.kecamatan = item.kode;
        this.qKecamatan = item.nama;
        this.open = '';
    },
}));

Alpine.data('firmPicker', (calon = {}) => ({
    q: '',
    area: 'all',
    rev: 0,
    calon,
    matches(el) {
        this.rev;
        if (el.querySelector('input:checked')) {
            return true;
        }
        const needle = this.q.trim().toLowerCase();
        const name = (el.dataset.name || '').toLowerCase();
        const wilayah = (el.dataset.wilayah || '').toLowerCase();
        if (needle && ! name.includes(needle) && ! wilayah.includes(needle)) {
            return false;
        }
        if (this.area === 'same_kecamatan') {
            return el.dataset.kecamatan === this.calon.kecamatan;
        }
        if (this.area === 'same_kota') {
            return el.dataset.kota === this.calon.kota;
        }
        if (this.area === 'same_provinsi') {
            return el.dataset.provinsi === this.calon.provinsi;
        }

        return true;
    },
}));

Alpine.start();
