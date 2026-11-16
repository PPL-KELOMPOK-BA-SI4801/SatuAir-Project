<?php

namespace App\Http\Controllers\Masyarakat;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Complaint;
use App\Models\ComplaintCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ComplaintController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = Complaint::where('user_id', $user->id)
            ->with('category');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        $complaints = $query->orderBy('created_at', 'desc')
            ->paginate(10)
            ->appends($request->query());

        $categories = ComplaintCategory::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('masyarakat.complaints.index', compact('complaints', 'categories'));
    }

    public function create()
    {
        $categories = ComplaintCategory::where('is_active', true)
            ->orderBy('name')
            ->get();

        $areas = Area::where('is_active', true)->orderBy('name')->get();

        return view('masyarakat.complaints.create', compact('categories', 'areas'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'category_id'  => ['required', 'exists:complaint_categories,id'],
            'area_id'      => ['nullable', 'exists:areas,id'],
            'location'     => ['required', 'string', 'max:500'],
            'description'  => ['required', 'string', 'max:2000'],
            'photo'        => ['nullable', 'image', 'max:2048'],
            'occurred_at'  => ['nullable', 'date'],
        ], [
            'category_id.required'  => 'Kategori pengaduan wajib dipilih.',
            'category_id.exists'    => 'Kategori pengaduan tidak valid.',
            'location.required'     => 'Lokasi kejadian wajib diisi.',
            'location.max'          => 'Lokasi kejadian maksimal 500 karakter.',
            'description.required'  => 'Deskripsi pengaduan wajib diisi.',
            'description.max'       => 'Deskripsi pengaduan maksimal 2000 karakter.',
            'photo.image'           => 'Foto harus berupa gambar.',
            'photo.max'             => 'Foto maksimal 2MB.',
            'occurred_at.date'      => 'Format tanggal kejadian tidak valid.',
        ]);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('complaint-photos', 'public');
        }

        $complaint = Complaint::create([
            'user_id'     => $user->id,
            'category_id' => $request->category_id,
            'area_id'     => $request->area_id,
            'location'    => $request->location,
            'description' => $request->description,
            'photo'       => $photoPath,
            'occurred_at' => $request->occurred_at ?? now(),
            'status'      => 'pending',
        ]);

        return redirect()
            ->route('masyarakat.complaints.index')
            ->with('success', "Pengaduan berhasil dikirim dengan nomor tiket: {$complaint->ticket_number}");
    }

    public function show($id)
    {
        $user = Auth::user();

        $complaint = Complaint::where('user_id', $user->id)
            ->with(['category', 'area', 'updates' => function ($query) {
                $query->orderBy('created_at', 'asc');
            }, 'updates.user'])
            ->findOrFail($id);

        return view('masyarakat.complaints.show', compact('complaint'));
    }
}
