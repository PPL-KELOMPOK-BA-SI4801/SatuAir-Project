<?php

namespace App\Http\Controllers\Bendahara;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ComplaintController extends Controller
{
    public function index(Request $request)
    {
        if (!in_array(Auth::user()?->role, ['bendahara', 'admin'])) {
            abort(403, 'Akses ditolak.');
        }

        $query = Complaint::with(['user', 'category', 'area', 'assignee'])->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $complaints = $query->paginate(15)->withQueryString();

        return view('bendahara.complaints.index', compact('complaints'));
    }
}
