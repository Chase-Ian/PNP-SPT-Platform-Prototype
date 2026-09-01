<?php
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\CertificateController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ModuleController as AdminModuleController;

if (app()->environment(['local', 'staging'])) {
    Route::post('/dev/quick-login/{type}', function (string $type) {
        abort_unless(in_array($type, ['trainee', 'supervisor', 'admin']), 404);

        $emails = [
            'trainee' => 'maria.cruz@pnp.gov.ph',
            'supervisor' => 'supervisor.demo@pnp.gov.ph',
            'admin' => 'admin.demo@pnp.gov.ph',
        ];

        $user = User::where('email', $emails[$type])->firstOrFail();
        Auth::login($user);

        return redirect(match ($type) {
            'admin' => '/admin/dashboard',
            'supervisor' => '/supervisor/dashboard',
            default => '/dashboard',
        });
    })->name('dev.quick-login');
}

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// --- General authenticated routes (trainee + supervisor + admin all use these) ---
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/courses', [CourseController::class, 'index'])->name('courses.index');
    Route::post('/courses/{course}/enroll', [CourseController::class, 'enroll'])->name('courses.enroll');
    Route::post('/courses/{course}/drop', [CourseController::class, 'drop'])->name('courses.drop');

    Route::get('/certificates', [CertificateController::class, 'index'])->name('certificates.index');
    Route::get('/certificates/{certificate}/download', [CertificateController::class, 'download'])->name('certificates.download');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
});

// --- Supervisor routes ---
Route::middleware(['auth', 'role:supervisor'])->prefix('supervisor')->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Supervisor/Dashboard');
    })->name('supervisor.dashboard');

    Route::get('/monitoring', function () {
        return Inertia::render('Supervisor/Monitoring');
    })->name('supervisor.monitoring');
});

// --- Admin routes ---
Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');

    Route::get('/modules', [AdminModuleController::class, 'index'])->name('admin.modules.index');
    Route::post('/modules', [AdminModuleController::class, 'store'])->name('admin.modules.store');
    Route::delete('/modules/{module}', [AdminModuleController::class, 'destroy'])->name('admin.modules.destroy');
    Route::get('/students', fn () => Inertia::render('Admin/Students/Index'))->name('admin.students.index');
    Route::get('/exam-settings', fn () => Inertia::render('Admin/ExamSettings/Index'))->name('admin.exam-settings.index');
    Route::get('/certificates', fn () => Inertia::render('Admin/Certificates/Index'))->name('admin.certificates.index');
    Route::get('/analytics', fn () => Inertia::render('Admin/Analytics/Index'))->name('admin.analytics.index');
});

// --- Public — no auth, matches the PDF's Certificate Verification screen ---
Route::get('/verify', function () {
    return Inertia::render('Verify/Index');
})->name('certificates.verify.form');

Route::get('/verify/{serial}', [CertificateController::class, 'verify'])->name('certificates.verify');

require __DIR__.'/auth.php';