@props(['banners'])
@foreach ($banners as $banner)
    <div class="rounded-xl border border-[#c9d9f0] bg-tag-info-bg px-4 py-4 md:px-5" role="status">
        @if ($banner->imageUrl())
            <img src="{{ $banner->imageUrl() }}" alt="" class="mb-3 w-full max-h-48 object-cover rounded-lg">
        @endif
        <p class="font-medium text-navy">{{ $banner->title }}</p>
        <div class="prose prose-sm max-w-none mt-2 text-ink">{!! $banner->body !!}</div>
    </div>
@endforeach
