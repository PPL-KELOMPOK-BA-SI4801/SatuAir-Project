<?php

namespace App\Http\Controllers\Bendahara;

use App\Http\Controllers\Controller;
use App\Models\MeterReading;
use App\Models\MeterReadingReview;
use App\Services\HitungPemakaianMeter;
use App\Services\PemeriksaanPemakaian;
use Illuminate\Http\Request;

class MeterReadingReviewController extends Controller
{
    public function index(Request $request, PemeriksaanPemakaian $service)
    {
        $filter = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'period' => ['nullable', 'regex:/^\d{4}-(0[1-9]|1[0-2])$/'],
            'status' => ['nullable', 'in:pending,draft,returned,approved'],
        ]);
        $query = MeterReading::with(['waterMeter.user', 'latestReview.reviewer'])->where('requires_review', true);
        if ($search = $filter['search'] ?? null) {
            $query->whereHas('waterMeter', fn ($meter) => $meter->where('meter_number', 'like', '%'.$search.'%')
                ->orWhereHas('user', fn ($user) => $user->where('name', 'like', '%'.$search.'%')->orWhere('customer_id', 'like', '%'.$search.'%')));
        }
        if ($period = $filter['period'] ?? null) $query->whereBetween('reading_date', [$period.'-01', \Carbon\Carbon::parse($period.'-01')->endOfMonth()->toDateString()]);
        $status = $filter['status'] ?? null;
        if ($status === 'pending') $query->whereNull('verified_at');
        elseif ($status === 'approved') $query->whereNotNull('verified_at');
        elseif ($status) $query->whereHas('latestReview', fn ($review) => $review->where('status', $status));
        $readings = $query->orderByDesc('reading_date')->orderByDesc('id')->paginate(15)->withQueryString();
        $usages = $readings->getCollection()->mapWithKeys(fn ($reading) => [$reading->id => $service->snapshot($reading)['usage_m3']]);
        $pending = MeterReading::where('requires_review', true)->whereNull('verified_at')->count();
        return view('bendahara.reviews.index', compact('readings', 'pending', 'usages'));
    }

    public function reading(int $reading, PemeriksaanPemakaian $service)
    {
        return view('bendahara.reviews.reading', $this->data($reading, $service));
    }

    public function create(int $reading, PemeriksaanPemakaian $service)
    {
        $data = $this->data($reading, $service);
        if ($data['lockedReason']) return redirect()->route('bendahara.reviews.reading', $reading)->withErrors(['review' => $data['lockedReason']]);
        return view('bendahara.reviews.form', $data + ['review' => null, 'stale' => false]);
    }

    public function store(Request $request, int $reading, PemeriksaanPemakaian $service)
    {
        $input = $this->input($request);
        $review = $service->create($reading, $input['revision'], $input, $request->user());
        return redirect()->route('bendahara.reviews.show', $review)->with('success', 'Draf pemeriksaan berhasil disimpan. Tagihan belum diterbitkan.');
    }

    public function show(int $review, PemeriksaanPemakaian $service)
    {
        $review = MeterReadingReview::withTrashed()->with('reviewer')->findOrFail($review);
        $data = $this->data($review->meter_reading_id, $service);
        $review->setRelation('reading', $data['reading']);
        return view('bendahara.reviews.show', $data + ['review' => $review, 'stale' => $review->reading_revision !== $data['reading']->reviewRevision()]);
    }

    public function edit(Request $request, int $review, PemeriksaanPemakaian $service)
    {
        $review = MeterReadingReview::findOrFail($review);
        abort_unless($review->reviewer_id === $request->user()->id, 403);
        $data = $this->data($review->meter_reading_id, $service);
        if ($review->status !== 'draft' || $data['lockedReason']) return redirect()->route('bendahara.reviews.show', $review)->withErrors(['review' => $data['lockedReason'] ?? 'Pemeriksaan sudah final.']);
        $review->setRelation('reading', $data['reading']);
        return view('bendahara.reviews.form', $data + ['review' => $review, 'stale' => $review->reading_revision !== $data['reading']->reviewRevision()]);
    }

    public function update(Request $request, int $review, PemeriksaanPemakaian $service)
    {
        $input = $this->input($request);
        $service->update($review, $input['revision'], $input, $request->user());
        return redirect()->route('bendahara.reviews.show', $review)->with('success', 'Perubahan draf pemeriksaan tersimpan.');
    }

    public function destroy(Request $request, int $review, PemeriksaanPemakaian $service)
    {
        $input = $request->validate(['revision' => ['required', 'string', 'size:64']]);
        $record = $service->cancel($review, $input['revision'], $request->user());
        return redirect()->route('bendahara.reviews.reading', $record->meter_reading_id)->with('success', 'Draf dibatalkan. Riwayat tetap tersimpan dan pencatatan Petugas tidak dihapus.');
    }

    public function finalize(Request $request, int $review, PemeriksaanPemakaian $service)
    {
        $input = $request->validate(['revision' => ['required', 'string', 'size:64'], 'decision' => ['required', 'in:approved,returned']]);
        $service->finalize($review, $input['revision'], $input['decision'], $request->user());
        return redirect()->route('bendahara.reviews.show', $review)->with('success', $input['decision'] === 'approved'
            ? 'Pemeriksaan disetujui. Tagihan berhasil diterbitkan.' : 'Pencatatan dikembalikan kepada Petugas untuk dikoreksi. Tagihan belum dibuat.');
    }

    private function data(int $id, PemeriksaanPemakaian $service): array
    {
        $reading = MeterReading::withTrashed()->with(['waterMeter.user', 'recorder', 'latestReview', 'bill',
            'reviews' => fn ($query) => $query->withTrashed()->with('reviewer')->latest('id')])->findOrFail($id);
        $snapshot = $service->snapshot($reading);
        $hitung = app(HitungPemakaianMeter::class);
        return [
            'reading' => $reading, 'snapshot' => $snapshot, 'lockedReason' => $service->lockedReason($reading),
            'awal' => $hitung->angkaKeSatuan($snapshot['previous_value']), 'akhir' => $hitung->angkaKeSatuan($snapshot['reading_value']),
        ];
    }

    private function input(Request $request): array
    {
        $input = $request->validate([
            'revision' => ['required', 'string', 'size:64'], 'notes' => ['nullable', 'string', 'max:1000'],
            'checks' => ['sometimes', 'array:identity_period,meter_usage'],
            'checks.identity_period' => ['sometimes', 'boolean'], 'checks.meter_usage' => ['sometimes', 'boolean'],
        ]);
        $input['checks'] = ['identity_period' => $request->boolean('checks.identity_period'), 'meter_usage' => $request->boolean('checks.meter_usage')];
        return $input;
    }
}
