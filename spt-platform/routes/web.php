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

Route::middleware('auth')->group(function () {
    Route::get('/admin/dashboard', function () {
        return Inertia::render('Admin/Dashboard');
    })->name('admin.dashboard');

    Route::get('/supervisor/dashboard', function () {
        return Inertia::render('Supervisor/Dashboard');
    })->name('supervisor.dashboard');

    Route::get('/supervisor/monitoring', function () {
        return Inertia::render('Supervisor/Monitoring');
    })->name('supervisor.monitoring');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/courses', [CourseController::class, 'index'])->name('courses.index');
    Route::post('/courses/{course}/enroll', [CourseController::class, 'enroll'])->name('courses.enroll');
    Route::post('/courses/{course}/drop', [CourseController::class, 'drop'])->name('courses.drop');
    Route::get('/certificates', [CertificateController::class, 'index'])->name('certificates.index');
    Route::get('/certificates/{certificate}/download', [CertificateController::class, 'download'])->name('certificates.download');
    Route::get('/verify/{serial}', [CertificateController::class, 'verify'])->name('certificates.verify');
});

// Public — no auth, matches the PDF's Certificate Verification screen
Route::get('/verify/{hash}', [CertificateController::class, 'verify'])->name('certificates.verify');
Route::get('/verify', function () {
    return Inertia::render('Verify/Index');
})->name('certificates.verify.form');

require __DIR__.'/auth.php';
