<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#0d2a5c">

        <title>{{ config('app.name') }}</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-ink antialiased">
        <div class="min-h-screen lg:grid lg:grid-cols-2">
            <div class="relative hidden lg:flex flex-col justify-between bg-gradient-to-br from-navy via-navy to-navy-dark text-white p-12 overflow-hidden">
                <div class="absolute inset-0 opacity-[0.07]" style="background-image: radial-gradient(circle at 1px 1px, white 1px, transparent 0); background-size: 24px 24px;"></div>
                <div class="relative">
                    <p class="font-serif text-3xl leading-tight">DPC PERADI</p>
                    <p class="text-xs tracking-[0.22em] text-sidebar-muted mt-1">JAKARTA BARAT</p>
                </div>
                <div class="relative max-w-md">
                    <p class="text-[11px] font-medium tracking-[0.16em] uppercase text-accent-muted">Kantong Magang Advokat</p>
                    <h1 class="mt-4 font-serif text-2xl leading-snug">Portal resmi magang wajib calon advokat</h1>
                    <p class="mt-4 text-sm text-sidebar-muted leading-relaxed">
                        Kelola lamaran, logbook digital, dan verifikasi berkas sumpah dalam satu platform terintegrasi untuk DPC PERADI Jakarta Barat.
                    </p>
                    <ul class="mt-8 space-y-3 text-sm text-sidebar-muted">
                        <li class="flex gap-2"><span class="text-accent">●</span> Logbook harian & tanda tangan pendamping</li>
                        <li class="flex gap-2"><span class="text-accent">●</span> Verifikasi dual-level Admin DPC</li>
                        <li class="flex gap-2"><span class="text-accent">●</span> Monitoring kepatuhan & audit akhir</li>
                    </ul>
                </div>
                <p class="relative text-xs text-sidebar-dim">© DPC PERADI Jakarta Barat</p>
            </div>

            <div class="flex min-h-screen flex-col items-center justify-center bg-surface px-4 py-10 sm:px-8">
                <div class="lg:hidden text-center mb-8">
                    <p class="font-serif text-2xl text-navy">DPC PERADI</p>
                    <p class="text-xs tracking-[0.2em] text-muted-foreground mt-0.5">JAKARTA BARAT</p>
                    <p class="text-xs text-muted-foreground mt-2">Kantong Magang Advokat</p>
                </div>

                <div class="w-full max-w-md sm:max-w-lg app-card app-guest-enter px-6 py-8 sm:px-8 sm:py-9">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </body>
</html>
