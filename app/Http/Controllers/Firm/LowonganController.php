<?php

namespace App\Http\Controllers\Firm;

use App\Http\Controllers\Controller;
use App\Models\JobPosting;
use App\Models\LawFirm;
use App\Support\WilayahHierarchy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LowonganController extends Controller
{
    public function index(): View
    {
        $firm = $this->firm();

        return view('firm.lowongan', [
            'firm' => $firm,
            'jobPostings' => $firm->jobPostings()->orderBy('title')->get(),
        ]);
    }

    public function edit(JobPosting $jobPosting): View
    {
        $this->assertOwn($jobPosting);

        return view('firm.lowongan-form', [
            'firm' => $this->firm(),
            'jobPosting' => $jobPosting,
        ]);
    }

    public function update(Request $request, JobPosting $jobPosting): RedirectResponse
    {
        $this->assertOwn($jobPosting);
        $jobPosting->update($this->payload($request));

        return redirect()->route('firm.lowongan')->with('status', 'Lowongan '.$jobPosting->title.' disimpan.');
    }

    private function firm(): LawFirm
    {
        return Auth::user()->supervisingLawyer->lawFirm;
    }

    private function assertOwn(JobPosting $jobPosting): void
    {
        abort_unless($jobPosting->law_firm_id === $this->firm()->id, 403);
    }

    /** @return array{title: string, description: ?string, practice_areas: list<string>, quota: int, status: string, provides_transport: bool, kabupaten_kota_kode: string} */
    private function payload(Request $request): array
    {
        $data = $request->validate(array_merge([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'practice_areas' => ['nullable', 'string', 'max:500'],
            'quota' => ['required', 'integer', 'min:1', 'max:100'],
            'status' => ['required', 'in:ACTIVE,INACTIVE'],
            'provides_transport' => ['sometimes', 'boolean'],
        ], WilayahHierarchy::kabupatenRules()));

        if (! WilayahHierarchy::kabupatenIsConsistent($data['provinsi_kode'], $data['kabupaten_kota_kode'])) {
            throw ValidationException::withMessages([
                'kabupaten_kota_kode' => 'Kota/kabupaten harus berada di provinsi yang dipilih.',
            ]);
        }

        $areas = collect(explode(',', (string) ($data['practice_areas'] ?? '')))
            ->map(fn (string $area) => trim($area))
            ->filter()
            ->values()
            ->all();

        return [
            'title' => $data['title'],
            'description' => $data['description'] ?: null,
            'practice_areas' => $areas,
            'quota' => (int) $data['quota'],
            'status' => $data['status'],
            'provides_transport' => $request->boolean('provides_transport'),
            'kabupaten_kota_kode' => $data['kabupaten_kota_kode'],
        ];
    }
}
