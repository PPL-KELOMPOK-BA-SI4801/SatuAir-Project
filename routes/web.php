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
use App\Http\Controllers\Masyarakat\MeterHistoryController as MasyarakatMeterHistoryController;
use App\Http\Controllers\Petugas\DashboardController as PetugasDashboardController;
use App\Http\Controllers\Petugas\MeterReadingController as PetugasMeterReadingController;
use App\Http\Controllers\Petugas\MeterReadingCorrectionController;
use App\Http\Controllers\Petugas\PaymentController as PetugasPaymentController;
use App\Http\Controllers\Petugas\ComplaintController as PetugasComplaintController;
use App\Http\Controllers\Bendahara\DashboardController as BendaharaDashboardController;
use App\Http\Controllers\Bendahara\RekapController as BendaharaRekapController;
use App\Http\Controllers\Bendahara\UserController as BendaharaUserController;
use App\Http\Controllers\Bendahara\ComplaintController as BendaharaComplaintController;
use App\Http\Controllers\Bendahara\AccessControlController;
use App\Http\Controllers\Bendahara\MeterReadingReviewController;

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

// Kompatibilitas pencatatan yang sudah dibuat pada branch PBI-09.
require __DIR__.'/meter.php';

// Fallback for navbar 'profil.index'
Route::middleware('auth')->get('/profil', function () {
    $user = auth()->user();
    if ($user->role === 'masyarakat') {
        return redirect()->route('masyarakat.profile.show');
    }
    if ($user->role === 'bendahara') {
        return redirect()->route('bendahara.profile.edit');
    }
    return redirect()->back();
})->name('profil.index');

Route::middleware(['auth', 'role:bendahara'])
    ->prefix('bendahara')->name('bendahara.')
    ->group(function () {
        Route::get('/profil', [BendaharaUserController::class, 'editOwnProfile'])->name('profile.edit');
        Route::put('/profil', [BendaharaUserController::class, 'updateOwnProfile'])->name('profile.update');
    });

// Masyarakat Routes
Route::middleware(['auth', 'permission:resident.use'])->prefix('masyarakat')->name('masyarakat.')->group(function () {
    Route::get('/dashboard', [MasyarakatDashboardController::class, 'index'])->name('dashboard');

    // Riwayat pencatatan meter
    Route::get('/meter-history', [MasyarakatMeterHistoryController::class, 'index'])->name('meter-history.index');

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
Route::middleware(['auth', 'permission:field.manage'])->prefix('petugas')->name('petugas.')->group(function () {
    Route::get('/dashboard', [PetugasDashboardController::class, 'index'])->name('dashboard');

    Route::get('/meters', [PetugasMeterReadingController::class, 'index'])->name('meters.index');
    Route::get('/meters/{id}/catat', [PetugasMeterReadingController::class, 'create'])->name('meters.create');
    Route::post('/meters', [PetugasMeterReadingController::class, 'store'])->name('meters.store');

    Route::get('/readings/{reading}', [MeterReadingCorrectionController::class, 'show'])->whereNumber('reading')->name('readings.show');
    Route::get('/readings/{reading}/edit', [MeterReadingCorrectionController::class, 'edit'])->whereNumber('reading')->name('readings.edit');
    Route::patch('/readings/{reading}', [MeterReadingCorrectionController::class, 'update'])->whereNumber('reading')->name('readings.update');
    Route::post('/readings/{reading}/cancel', [MeterReadingCorrectionController::class, 'cancel'])->whereNumber('reading')->name('readings.cancel');

    Route::get('/complaints', [PetugasComplaintController::class, 'index'])->name('complaints.index');
    Route::get('/complaints/{id}', [PetugasComplaintController::class, 'show'])->name('complaints.show');
    Route::put('/complaints/{id}', [PetugasComplaintController::class, 'update'])->name('complaints.update');
});

Route::middleware(['auth', 'permission:payments.view'])->prefix('petugas')->name('petugas.')->group(function () {
    Route::get('/payments', [PetugasPaymentController::class, 'index'])->name('payments.index');
    Route::post('/payments/kantor', [PetugasPaymentController::class, 'storeOffice'])->name('payments.office');
    Route::get('/payments/{id}', [PetugasPaymentController::class, 'show'])->name('payments.show');
});

// Pemeriksaan meter memiliki izin tersendiri supaya bisa diatur terpisah.
Route::middleware(['auth', 'permission:meter.review'])->prefix('bendahara')->name('bendahara.')->group(function () {
    Route::get('/pemeriksaan', [MeterReadingReviewController::class, 'index'])->name('reviews.index');
    Route::get('/pemeriksaan/pencatatan/{reading}', [MeterReadingReviewController::class, 'reading'])->whereNumber('reading')->name('reviews.reading');
    Route::get('/pemeriksaan/pencatatan/{reading}/buat', [MeterReadingReviewController::class, 'create'])->whereNumber('reading')->name('reviews.create');
    Route::post('/pemeriksaan/pencatatan/{reading}', [MeterReadingReviewController::class, 'store'])->whereNumber('reading')->name('reviews.store');
    Route::get('/pemeriksaan/{review}', [MeterReadingReviewController::class, 'show'])->whereNumber('review')->name('reviews.show');
    Route::get('/pemeriksaan/{review}/edit', [MeterReadingReviewController::class, 'edit'])->whereNumber('review')->name('reviews.edit');
    Route::patch('/pemeriksaan/{review}', [MeterReadingReviewController::class, 'update'])->whereNumber('review')->name('reviews.update');
    Route::delete('/pemeriksaan/{review}', [MeterReadingReviewController::class, 'destroy'])->whereNumber('review')->name('reviews.destroy');
    Route::post('/pemeriksaan/{review}/finalisasi', [MeterReadingReviewController::class, 'finalize'])->whereNumber('review')->name('reviews.finalize');
});

// Fitur keuangan dapat diberikan kepada peran mana pun melalui halaman Hak Akses.
Route::middleware(['auth', 'permission:finance.manage'])->prefix('bendahara')->name('bendahara.')->group(function () {
    Route::get('/dashboard', [BendaharaDashboardController::class, 'index'])->name('dashboard')->middleware('permission:dashboard.bendahara');
    Route::get('/rekap', [BendaharaRekapController::class, 'index'])->name('rekap.index');
    Route::post('/rekap/lock', [BendaharaRekapController::class, 'lock'])->name('rekap.lock');
    Route::get('/pembayaran', [BendaharaRekapController::class, 'pembayaran'])->name('pembayaran');
    Route::post('/pembayaran', [BendaharaRekapController::class, 'storePembayaran'])->name('pembayaran.store');
    Route::get('/notifikasi', [BendaharaRekapController::class, 'notifications'])->name('notifications.index');
    Route::get('/pengguna', [BendaharaUserController::class, 'index'])->name('pengguna.index')->middleware('permission:users.manage');
    Route::get('/pengguna/{user}', [BendaharaUserController::class, 'showCustomer'])->name('pengguna.show')->middleware('permission:users.manage');
    Route::get('/pengguna/{user}/profil/edit', [BendaharaUserController::class, 'editCustomerProfile'])->name('pengguna.profile.edit')->middleware('permission:users.manage');
    Route::put('/pengguna/{user}/profil', [BendaharaUserController::class, 'updateCustomerProfile'])->name('pengguna.profile.update')->middleware('permission:users.manage');
    Route::get('/pengguna/{user}/edit', [BendaharaUserController::class, 'edit'])->name('pengguna.edit')->middleware('permission:users.manage');
    Route::put('/pengguna/{user}', [BendaharaUserController::class, 'update'])->name('pengguna.update')->middleware('permission:users.manage');
});

Route::middleware(['auth', 'permission:field.manage'])->prefix('bendahara')->name('bendahara.')->group(function () {
    Route::get('/pengaduan', [BendaharaComplaintController::class, 'index'])->name('complaints.index');
});

Route::middleware(['auth', 'role:bendahara', 'permission:access.manage'])
    ->prefix('bendahara')->name('bendahara.')
    ->group(function () {
        Route::get('/hak-akses', [AccessControlController::class, 'index'])->name('access.index');
        Route::put('/hak-akses/{role}', [AccessControlController::class, 'update'])->name('access.update');
});
