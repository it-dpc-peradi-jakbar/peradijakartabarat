# Kantong Magang Advokat — DPC PERADI Jakarta Barat

Platform web untuk mengelola program magang wajib 24 bulan bagi calon advokat (alumni lulus UPA) di bawah DPC PERADI Jakarta Barat, dibangun dari mockup `Platform_Magang_Fase_1`. Dibangun dengan **Laravel 10** dan **MySQL** (PHP **8.1+**).

Dokumentasi sistem (bisnis, database, aplikasi): [`docs/README.md`](docs/README.md).

## Peran pengguna

Aplikasi memiliki tiga peran dengan login terpisah (satu akun = satu peran):

- **Calon Advokat** — dashboard alur magang, cari & melamar lowongan, riwayat lamaran, logbook digital harian, paket berkas sumpah.
- **Law Firm** (advokat pendamping) — dashboard & kuota bimbingan, review pelamar & terbitkan surat penerimaan, review & tanda tangani logbook.
- **Admin DPC** — verifikasi dua level (calon advokat & kantor hukum/pendamping), pencocokan calon↔kantor, monitoring kepatuhan logbook dan audit akhir kelayakan sumpah.

## Docker (lokal, recommended for testing)

```bash
docker compose up --build
```

App: http://localhost:8000  
MySQL from the host: `127.0.0.1:3307` (user/password/database `peradi`).

First boot runs `composer install`, `npm run build`, migrate, `wilayah:import`, and seed. Later boots skip seed (flag `storage/app/.docker_seeded`) but still import wilayah. Compose also writes Docker DB settings into `.env` (`DB_HOST=mysql`). Reset everything:

```bash
docker compose down -v
rm -f storage/app/.docker_seeded
docker compose up --build
```

## Instalasi lokal (tanpa Docker)

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
```

Buat database MySQL lalu sesuaikan kredensial di `.env`:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=peradi_jakbar
DB_USERNAME=peradi
DB_PASSWORD=
```

Jalankan migrasi beserta data contoh:

```bash
php artisan migrate --seed
npm run build
php artisan serve
```

## Akun demo

**Password untuk semua akun di bawah:** `password` (konstanta `Database\Seeders\DemoUserCatalog::PASSWORD`).

### Login utama (satu per peran)

| Peran | Email | Catatan |
|---|---|---|
| Calon Advokat | `andi@peradijakbar.test` | Magang 14/24 bulan, Wibisono & Rekan |
| Law Firm / Pendamping | `lawfirm@peradijakbar.test` | Dr. Hendra Wibisono — pelamar & logbook |
| Admin DPC | `admin@peradijakbar.test` | Verifikasi & monitoring |

### Calon advokat tambahan (password sama)

| Email | Kode CA | Skenario singkat |
|---|---|---|
| `andi@peradijakbar.test` | CA-2025-0417 | Lamaran diterima, logbook & berkas aktif |
| `rizky@peradijakbar.test` | CA-2024-0288 | 22/24 bulan, audit akhir berjalan |
| `nadia@peradijakbar.test` | CA-2025-0512 | 11/24 bulan, logbook rapi |
| `fajar@peradijakbar.test` | CA-2026-0031 | 3/24 bulan, menunggu ttd rekap |
| `maya@peradijakbar.test` | CA-2026-0058 | Admin belum verifikasi, lamaran interview |
| `bagus@peradijakbar.test` | CA-2025-0349 | Perlu perbaikan verifikasi, CV review |
| `laras@peradijakbar.test` | CA-2026-0072 | Verifikasi pending, CV review |
| `rina@peradijakbar.test` | CA-2025-0201 | Lamaran ditolak |
| `indra@peradijakbar.test` | CA-2025-0155 | Magang di Santika & Partners |
| `dewi@peradijakbar.test` | CA-2024-0090 | 24/24 bulan, audit lulus, berkas lengkap |
| `yoga@peradijakbar.test` | CA-2024-0102 | 24/24 bulan, audit berkas kurang |
| `sinta@peradijakbar.test` | CA-2025-0233 | LBH Trisakti, 9 bulan |
| `dimas@peradijakbar.test` | CA-2026-0101 | Terverifikasi, lamaran SUBMITTED (tes Terima) |
| `putri@peradijakbar.test` | CA-2026-0102 | Terverifikasi, belum melamar (tes Lamar) |
| `eko@peradijakbar.test` | CA-2026-0103 | Admisi belum unggah (tes antrian admin) |
| `hana@peradijakbar.test` | CA-2026-0104 | Logbook telat 20 hari (tes alert monitoring) |
| `budi@peradijakbar.test` | CA-2026-0105 | Ardiansyah, 8/24 bulan |
| `citra@peradijakbar.test` | CA-2026-0106 | Lamaran ke Santika |

### Law firm tambahan (password sama)

| Email | Kantor |
|---|---|
| `santika@peradijakbar.test` | Santika, Hartono & Partners |
| `ardiansyah@peradijakbar.test` | Kantor Hukum Ardiansyah |
| `lbh@peradijakbar.test` | LBH Trisakti |
| `wijaya@peradijakbar.test` | Wijaya Legal Consult (PENDING) |
| `kusuma@peradijakbar.test` | Kusuma & Associates (NEEDS_CORRECTION) |

Daftar lengkap juga di `database/seeders/DemoUserCatalog.php`. Jalankan ulang data demo: `php artisan migrate:fresh --seed`.

Registrasi mandiri: Advocate candidate (`/register/advocate-candidate`, `/register` redirects), Law firm (`/register/law-firm`). Admin DPC hanya dari seeder / provisi DPC.

## Shared hosting (repo in `src/`)

**Login 419 PAGE EXPIRED?** Set `APP_URL` to the exact public URL (including `https://` and subfolder path). Run `php artisan config:clear`. Ensure `storage/framework/sessions` is writable. After deploy, log in again in a fresh tab.

**`storage:link` → `symlink()` undefined?** Verifikasi admisi calon advokat hanya menyimpan **tautan URL** berkas (Google Drive, dll.) — **`php artisan storage:link` tidak diperlukan** untuk fitur itu.

Same layout as other fchr.space Laravel apps: git clone lives in `src/`, `public/` contents (`index.php`, `.htaccess`, `build/`) sit beside `src/`. Point `index.php` at `src/vendor` and `src/bootstrap/app.php`; call `$app->usePublicPath(__DIR__)` only when `build/manifest.json` exists next to `index.php`. Run `npm run build` in `src/` (manifest defaults to `src/public/build/`). Set `APP_URL` and `SESSION_PATH` to the subfolder path. Avoid `route:cache` on the host; clear `bootstrap/cache/routes-v7.php` if `/` rejects GET.

## Struktur data inti

`LawFirm`, `SupervisingLawyer`, `CandidateAdvocate`, `JobPosting`, `InternshipApplication`, `LogbookEntry`, `MonthlyLogbookSummary`, `VerificationChecklist`, `OathDocument`, `FinalAudit` — lihat `database/migrations` dan `app/Models`.
