@props(['crumb', 'title', 'subtitle' => null, 'menu' => [], 'userMeta' => null])
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#0d2a5c">
    <title>{{ $title }} · {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="bg-surface text-ink font-sans antialiased">
    <div x-data="{ navOpen: false }" class="min-h-screen md:grid md:grid-cols-[260px_minmax(0,1fr)]">
        {{-- Mobile top bar --}}
        <header class="md:hidden sticky top-0 z-40 flex items-center justify-between gap-3 bg-navy/95 text-white px-4 py-3 backdrop-blur-md shadow-nav">
            <div class="min-w-0">
                <p class="font-serif text-base leading-tight truncate">DPC PERADI</p>
                <p class="text-[10px] tracking-[0.18em] text-sidebar-muted">JAKARTA BARAT</p>
            </div>
            <button
                type="button"
                @click="navOpen = true"
                class="inline-flex min-h-[44px] min-w-[44px] items-center justify-center rounded-xl border border-white/20 bg-white/5 text-sm font-medium transition hover:bg-white/10"
                aria-label="Buka menu"
            >
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                    <path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16"/>
                </svg>
            </button>
        </header>

        {{-- Mobile drawer backdrop --}}
        <div
            x-show="navOpen"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="navOpen = false"
            class="fixed inset-0 z-40 bg-navy-dark/60 backdrop-blur-sm md:hidden"
            x-cloak
        ></div>

        <aside
            :class="navOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'"
            class="fixed inset-y-0 left-0 z-50 flex w-[min(100%,280px)] flex-col gap-5 bg-gradient-to-b from-navy to-navy-dark py-6 text-white shadow-2xl transition-transform duration-300 ease-out md:static md:z-auto md:w-auto md:shadow-none md:sticky md:top-0 md:h-screen md:max-h-screen md:overflow-y-auto"
        >
            <div class="flex items-start justify-between gap-3 px-5 md:block">
                <div>
                    <p class="font-serif text-lg leading-tight">DPC PERADI</p>
                    <p class="text-xs tracking-[0.15em] text-sidebar-muted">JAKARTA BARAT</p>
                </div>
                <button
                    type="button"
                    @click="navOpen = false"
                    class="md:hidden inline-flex min-h-[40px] min-w-[40px] items-center justify-center rounded-lg text-sidebar-muted hover:bg-white/10 hover:text-white"
                    aria-label="Tutup menu"
                >
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" d="M6 6l12 12M18 6L6 18"/>
                    </svg>
                </button>
            </div>

            <div class="border-t border-white/10 mx-5"></div>

            <div class="px-5 flex-1 min-h-0 overflow-y-auto">
                <p class="text-[11px] tracking-[0.14em] text-sidebar-dim uppercase mb-2">Kantong Magang Advokat</p>
                <nav class="flex flex-col gap-1">
                    @foreach ($menu as $item)
                        <a href="{{ $item['route'] }}"
                           @click="navOpen = false"
                           @class([
                               ($item['active'] ?? false) ? 'app-sidebar-link-active' : 'app-sidebar-link-idle',
                           ])>
                            <span class="flex-1 text-left">{{ $item['label'] }}</span>
                            @if (! empty($item['badge']))
                                <span class="bg-accent text-navy-dark text-[11px] font-semibold rounded-full min-w-[22px] h-[22px] flex items-center justify-center px-1.5">{{ $item['badge'] }}</span>
                            @endif
                        </a>
                    @endforeach
                </nav>
            </div>

            <div class="mt-auto px-5 shrink-0">
                <div class="rounded-2xl border border-white/10 bg-white/5 px-4 py-3.5">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-white/15 text-white flex items-center justify-center text-sm font-semibold shrink-0 ring-2 ring-white/10">
                            {{ Illuminate\Support\Str::of(auth()->user()->name)->explode(' ')->map(fn ($w) => mb_substr($w, 0, 1))->take(2)->implode('') }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium truncate">{{ auth()->user()->name }}</p>
                            <p class="text-xs text-sidebar-muted truncate">
                                @switch(auth()->user()->role)
                                    @case('calon_advokat') Calon Advokat @break
                                    @case('law_firm') Law Firm @break
                                    @case('admin_dpc') Admin DPC @break
                                @endswitch
                            </p>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}" class="mt-3">
                        @csrf
                        <button type="submit" class="text-xs font-medium text-sidebar-muted hover:text-white transition">Keluar akun</button>
                    </form>
                </div>
            </div>
        </aside>

        <div class="flex flex-col min-w-0">
            <main class="px-4 sm:px-6 md:px-9 pt-5 md:pt-8 pb-16 md:pb-14 flex flex-col gap-5 md:gap-6 max-w-[1180px] w-full mx-auto">
                <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
                    <div class="min-w-0">
                        <p class="app-crumb">{{ $crumb }}</p>
                        <h1 class="app-page-title mt-1">{{ $title }}</h1>
                        @if ($subtitle)
                            <p class="text-sm text-ink-secondary mt-2 max-w-2xl leading-relaxed">{{ $subtitle }}</p>
                        @endif
                    </div>
                    <div class="hidden lg:flex app-card px-4 py-3 items-center gap-3 self-start">
                        <div class="w-10 h-10 rounded-full bg-tag-info-bg text-primary flex items-center justify-center text-sm font-semibold shrink-0">
                            {{ Illuminate\Support\Str::of(auth()->user()->name)->explode(' ')->map(fn ($w) => mb_substr($w, 0, 1))->take(2)->implode('') }}
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-semibold leading-tight truncate">{{ auth()->user()->name }}</p>
                            <p class="text-xs text-muted-foreground leading-tight truncate">{{ $userMeta }}</p>
                        </div>
                    </div>
                </div>

                @if (session('status'))
                    <div class="app-alert-success" role="status">{{ session('status') }}</div>
                @endif

                <div class="app-page-enter flex flex-col gap-5 md:gap-6">
                    {{ $slot }}
                </div>

                <p class="text-xs text-muted-foreground/80 mt-6 md:mt-10 text-center md:text-left">Platform Kantong Magang Advokat · DPC PERADI Jakarta Barat.</p>
            </main>
        </div>
    </div>
    @stack('scripts')
</body>
</html>
