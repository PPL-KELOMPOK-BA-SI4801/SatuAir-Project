<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Masyarakat\DashboardController as MasyarakatDashboardController;
use App\Http\Controllers\Masyarakat\BillController as MasyarakatBillController;
use App\Http\Controllers\Masyarakat\PaymentController as MasyarakatPaymentController;
use App\Http\Controllers\Masyarakat\ComplaintController as MasyarakatComplaintController;
use App\Http\Controllers\Masyarakat\ProfileController as MasyarakatProfileController;
use App\Http\Controllers\Petugas\DashboardController as PetugasDashboardController;
use App\Http\Controllers\Petugas\MeterReadingController as PetugasMeterReadingController;
use App\Http\Controllers\Petugas\PaymentController as PetugasPaymentController;
use App\Http\Controllers\Petugas\ComplaintController as PetugasComplaintController;
use App\Http\Controllers\Bendahara\DashboardController as BendaharaDashboardController;
use App\Http\Controllers\Bendahara\RekapController as BendaharaRekapController;
use App\Http\Controllers\Bendahara\UserController as BendaharaUserController;
use App\Http\Controllers\Bendahara\ComplaintController as BendaharaComplaintController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\RekapController as AdminRekapController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\ComplaintController as AdminComplaintController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Public Routes
Route::get('/', [HomeController::class, 'index'])->name('home');

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// Fallback for navbar 'profil.index'
Route::middleware('auth')->get('/profil', function () {
    $user = auth()->user();
    if ($user->role === 'masyarakat') {
        return redirect()->route('masyarakat.profile.show');
    }
    return redirect()->back();
})->name('profil.index');

// Masyarakat Routes
Route::middleware(['auth'])->prefix('masyarakat')->name('masyarakat.')->group(function () {
    Route::get('/dashboard', [MasyarakatDashboardController::class, 'index'])->name('dashboard');

    // Tagihan Air
    Route::get('/bills', [MasyarakatBillController::class, 'index'])->name('bills.index');
    Route::get('/bills/{id}', [MasyarakatBillController::class, 'show'])->name('bills.show');

    // Pembayaran Online (Model MyTelkomsel)
    Route::get('/bills/{id}/pay', [MasyarakatPaymentController::class, 'create'])->name('payments.create');
    Route::post('/payments', [MasyarakatPaymentController::class, 'store'])->name('payments.store');
    Route::get('/payments/{id}/instruction', [MasyarakatPaymentController::class, 'instruction'])->name('payments.instruction');
    Route::post('/payments/{id}/simulate-success', [MasyarakatPaymentController::class, 'simulateSuccess'])->name('payments.simulate-success');
    Route::post('/payments/{id}/cancel', [MasyarakatPaymentController::class, 'cancel'])->name('payments.cancel');
    Route::get('/payments/history', [MasyarakatPaymentController::class, 'history'])->name('payments.history');

    // Pengaduan Gangguan
    Route::get('/complaints', [MasyarakatComplaintController::class, 'index'])->name('complaints.index');
    Route::get('/complaints/create', [MasyarakatComplaintController::class, 'create'])->name('complaints.create');
    Route::post('/complaints', [MasyarakatComplaintController::class, 'store'])->name('complaints.store');
    Route::get('/complaints/{id}', [MasyarakatComplaintController::class, 'show'])->name('complaints.show');

    // Profil Pengguna
    Route::get('/profile', [MasyarakatProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile', [MasyarakatProfileController::class, 'update'])->name('profile.update');
});

// Petugas Routes
Route::middleware(['auth'])->prefix('petugas')->name('petugas.')->group(function () {
    Route::get('/dashboard', [PetugasDashboardController::class, 'index'])->name('dashboard');

    Route::get('/meters', [PetugasMeterReadingController::class, 'index'])->name('meters.index');
    Route::get('/meters/{id}/catat', [PetugasMeterReadingController::class, 'create'])->name('meters.create');
    Route::post('/meters', [PetugasMeterReadingController::class, 'store'])->name('meters.store');

    Route::get('/payments', [PetugasPaymentController::class, 'index'])->name('payments.index');
    Route::post('/payments/kantor', [PetugasPaymentController::class, 'storeOffice'])->name('payments.office');
    Route::get('/payments/{id}', [PetugasPaymentController::class, 'show'])->name('payments.show');
    Route::post('/payments/{id}/verify', [PetugasPaymentController::class, 'verify'])->name('payments.verify');
    Route::post('/payments/{id}/reject', [PetugasPaymentController::class, 'reject'])->name('payments.reject');

    Route::get('/complaints', [PetugasComplaintController::class, 'index'])->name('complaints.index');
    Route::get('/complaints/{id}', [PetugasComplaintController::class, 'show'])->name('complaints.show');
    Route::put('/complaints/{id}', [PetugasComplaintController::class, 'update'])->name('complaints.update');
});

// Bendahara Routes
Route::middleware(['auth'])->prefix('bendahara')->name('bendahara.')->group(function () {
    Route::get('/dashboard', [BendaharaDashboardController::class, 'index'])->name('dashboard');
    Route::get('/rekap', [BendaharaRekapController::class, 'index'])->name('rekap.index');
    Route::post('/rekap/lock', [BendaharaRekapController::class, 'lock'])->name('rekap.lock');
    Route::get('/pembayaran', [BendaharaRekapController::class, 'pembayaran'])->name('pembayaran');
    Route::post('/pembayaran', [BendaharaRekapController::class, 'storePembayaran'])->name('pembayaran.store');
    Route::get('/notifikasi', [BendaharaRekapController::class, 'notifications'])->name('notifications.index');
    Route::get('/pengguna', [BendaharaUserController::class, 'index'])->name('pengguna.index');
    Route::get('/pengguna/{user}/edit', [BendaharaUserController::class, 'edit'])->name('pengguna.edit');
    Route::put('/pengguna/{user}', [BendaharaUserController::class, 'update'])->name('pengguna.update');
    Route::get('/pengaduan', [BendaharaComplaintController::class, 'index'])->name('complaints.index');
});

// Admin Routes (alias kompatibel agar route admin.* & URL /admin tetap jalan lancar)
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/rekap', [AdminRekapController::class, 'index'])->name('rekap.index');
    Route::post('/rekap/lock', [AdminRekapController::class, 'lock'])->name('rekap.lock');
    Route::get('/pembayaran', [AdminRekapController::class, 'pembayaran'])->name('pembayaran');
    Route::post('/pembayaran', [AdminRekapController::class, 'storePembayaran'])->name('pembayaran.store');
    Route::get('/notifikasi', [AdminRekapController::class, 'notifications'])->name('notifications.index');
    Route::get('/pengguna', [AdminUserController::class, 'index'])->name('pengguna.index');
    Route::get('/pengguna/{user}/edit', [AdminUserController::class, 'edit'])->name('pengguna.edit');
    Route::put('/pengguna/{user}', [AdminUserController::class, 'update'])->name('pengguna.update');
    Route::get('/pengaduan', [AdminComplaintController::class, 'index'])->name('complaints.index');
});
