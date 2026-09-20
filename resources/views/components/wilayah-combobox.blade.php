@props([
    'id',
    'name',
    'label',
    'placeholder',
    'kode',
    'query',
    'items',
    'openKey',
    'pick',
    'disabled' => null,
])
<div class="relative" @click.outside="if (open === @js($openKey)) open = ''">
    <x-input-label :for="$id" :value="$label" />
    <input type="hidden" name="{{ $name }}" x-model="{{ $kode }}" required>
    <div class="relative mt-1.5">
        <input
            type="search"
            id="{{ $id }}"
            class="app-input w-full pr-9"
            autocomplete="off"
            placeholder="{{ $placeholder }}"
            x-model="{{ $query }}"
            @focus="open = @js($openKey)"
            @keydown.enter.prevent="(filtered({{ $items }}, {{ $query }}, {{ $kode }})[0]) && {{ $pick }}(filtered({{ $items }}, {{ $query }}, {{ $kode }})[0])"
            @keydown.escape.prevent="open = ''"
            @if ($disabled) :disabled="{{ $disabled }}" @endif
        >
        <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-muted-foreground text-xs">▾</span>
    </div>
    <ul
        x-show="open === @js($openKey) && ! {{ $disabled ?: 'false' }}"
        x-cloak
        class="absolute z-30 mt-1 max-h-56 w-full overflow-y-auto rounded-xl border border-line bg-white py-1 shadow-card"
        role="listbox"
    >
        <template x-for="item in filtered({{ $items }}, {{ $query }}, {{ $kode }})" :key="item.kode">
            <li>
                <button
                    type="button"
                    class="w-full px-3.5 py-2 text-left text-sm hover:bg-muted"
                    :class="item.kode === {{ $kode }} ? 'bg-tag-info-bg text-primary font-medium' : 'text-ink'"
                    @mousedown.prevent="{{ $pick }}(item)"
                >
                    <span x-text="item.nama"></span>
                </button>
            </li>
        </template>
        <li
            class="px-3.5 py-2 text-sm text-muted-foreground"
            x-show="filtered({{ $items }}, {{ $query }}, {{ $kode }}).length === 0"
        >Tidak ada hasil</li>
    </ul>
    <x-input-error :messages="$errors->get($name)" class="mt-2" />
</div>
