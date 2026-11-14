<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\ComplaintUpdate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Controller untuk pengelolaan pengaduan oleh petugas.
 *
 * Menampilkan daftar pengaduan, detail pengaduan,
 * dan update status pengaduan.
 */
class ComplaintController extends Controller
{
    /**
     * Tampilkan daftar pengaduan yang ditugaskan atau belum ditugaskan.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        if (!in_array($user->role, ['petugas', 'admin'])) {
            abort(403, 'Akses ditolak.');
        }

        $query = Complaint::with(['user', 'category', 'area', 'assignee'])
            ->orderByDesc('created_at');

        // Filter: assigned to this petugas or unassigned
        if ($request->filled('filter')) {
            $filter = $request->input('filter');
            if ($filter === 'mine') {
                $query->where('assigned_to', $user->id);
            } elseif ($filter === 'unassigned') {
                $query->whereNull('assigned_to');
            }
        } else {
            // Default: show complaints assigned to this petugas + unassigned
            $query->where(function ($q) use ($user) {
                $q->where('assigned_to', $user->id)
                  ->orWhereNull('assigned_to');
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('ticket_number', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $complaints = $query->paginate(15)->withQueryString();

        // Stats
        $pendingCount = Complaint::where(function ($q) use ($user) {
            $q->where('assigned_to', $user->id)->orWhereNull('assigned_to');
        })->where('status', 'pending')->count();

        $inProgressCount = Complaint::where(function ($q) use ($user) {
            $q->where('assigned_to', $user->id)->orWhereNull('assigned_to');
        })->where('status', 'in_progress')->count();

        $resolvedCount = Complaint::where(function ($q) use ($user) {
            $q->where('assigned_to', $user->id)->orWhereNull('assigned_to');
        })->where('status', 'resolved')->count();

        return view('petugas.complaints.index', compact(
            'complaints', 'pendingCount', 'inProgressCount', 'resolvedCount'
        ));
    }

    /**
     * Tampilkan detail pengaduan beserta timeline update.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $user = Auth::user();
        if (!in_array($user->role, ['petugas', 'admin'])) {
            abort(403, 'Akses ditolak.');
        }

        $complaint = Complaint::with([
            'user', 'category', 'area', 'assignee',
            'updates' => function ($q) {
                $q->with('user')->orderBy('created_at', 'asc');
            }
        ])->findOrFail($id);

        return view('petugas.complaints.show', compact('complaint'));
    }

    /**
     * Update status pengaduan dengan catatan dan foto opsional.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        if (!in_array($user->role, ['petugas', 'admin'])) {
            abort(403, 'Akses ditolak.');
        }

        $validated = $request->validate([
            'status' => 'required|in:verified,in_progress,resolved,rejected',
            'notes'  => 'required|string|max:1000',
            'photo'  => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
        ], [
            'status.required' => 'Status wajib dipilih.',
            'status.in'       => 'Status tidak valid.',
            'notes.required'  => 'Catatan update wajib diisi.',
            'notes.max'       => 'Catatan maksimal 1000 karakter.',
            'photo.image'     => 'File harus berupa gambar.',
            'photo.max'       => 'Ukuran foto maksimal 5MB.',
        ]);

        $complaint = Complaint::findOrFail($id);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('complaint-updates', 'public');
        }

        // Create a complaint update record
        ComplaintUpdate::create([
            'complaint_id' => $complaint->id,
            'user_id'      => $user->id,
            'status'       => $validated['status'],
            'notes'        => $validated['notes'],
            'photo'        => $photoPath,
        ]);

        // Update the complaint itself
        $updateData = [
            'status' => $validated['status'],
        ];

        // Assign to this petugas if not yet assigned
        if (empty($complaint->assigned_to)) {
            $updateData['assigned_to'] = $user->id;
        }

        if ($validated['status'] === 'resolved') {
            $updateData['resolved_at'] = now();
            $updateData['resolution_notes'] = $validated['notes'];
            if ($photoPath) {
                $updateData['resolution_photo'] = $photoPath;
            }
        }

        if ($validated['status'] === 'rejected') {
            $updateData['rejection_reason'] = $validated['notes'];
        }

        $complaint->update($updateData);

        $statusLabel = match ($validated['status']) {
            'verified' => 'diverifikasi',
            'rejected' => 'ditolak',
            'resolved' => 'diselesaikan',
            default => 'diproses',
        };

        return redirect()
            ->route('petugas.complaints.show', $complaint->id)
            ->with('success', 'Pengaduan ' . $complaint->ticket_number . ' berhasil ' . $statusLabel . '.');
    }
}
