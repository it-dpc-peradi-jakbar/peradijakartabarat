<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Support\HtmlSanitizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class BannerController extends Controller
{
    public function index(): View
    {
        return view('admin.banners.index', [
            'banners' => Banner::query()->orderByDesc('created_at')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.banners.form', ['banner' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        Banner::create([
            'created_by_user_id' => $request->user()->id,
            ...$this->payload($request),
        ]);

        return redirect()->route('admin.banners.index')->with('status', 'Pengumuman disimpan.');
    }

    public function edit(Banner $banner): View
    {
        return view('admin.banners.form', ['banner' => $banner]);
    }

    public function update(Request $request, Banner $banner): RedirectResponse
    {
        $banner->update($this->payload($request, $banner));

        return redirect()->route('admin.banners.index')->with('status', 'Pengumuman diperbarui.');
    }

    public function destroy(Banner $banner): RedirectResponse
    {
        $banner->deleteImage();
        $banner->delete();

        return redirect()->route('admin.banners.index')->with('status', 'Pengumuman dihapus.');
    }

    private function payload(Request $request, ?Banner $banner = null): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'target' => ['required', 'in:calon_advokat,law_firm'],
            'body' => ['required', 'string'],
            'expires_at' => ['nullable', 'date'],
            'image' => ['nullable', 'file', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
        ]);

        $body = HtmlSanitizer::reportBody($data['body']);
        if ($body === '') {
            throw ValidationException::withMessages([
                'body' => 'Isi pengumuman tidak boleh kosong.',
            ]);
        }

        $imagePath = $banner?->image_path;
        if ($request->boolean('remove_image') && $imagePath) {
            Storage::disk('public')->delete($imagePath);
            $imagePath = null;
        }
        if ($request->hasFile('image')) {
            if ($imagePath) {
                Storage::disk('public')->delete($imagePath);
            }
            $imagePath = $request->file('image')->store('banners', 'public');
        }

        return [
            'title' => $data['title'],
            'target' => $data['target'],
            'body' => $body,
            'image_path' => $imagePath,
            'expires_at' => $data['expires_at'] ?? null,
            'published_at' => $request->boolean('published')
                ? ($banner?->published_at ?? now())
                : null,
        ];
    }
}
