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
use App\Http\Controllers\Admin\StudentController as AdminStudentController;
use App\Http\Controllers\Admin\ExamSettingController as AdminExamSettingController;
use App\Http\Controllers\Admin\CertificateLogController;
use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Supervisor\MonitoringController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\Admin\LessonController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Admin\CourseController as AdminCourseController;
use App\Http\Controllers\Admin\ExamQuestionController;
use App\Http\Controllers\LessonViewController;
use App\Http\Controllers\LessonQuizController;

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

Route::get('/', function () {
    return redirect('/login');
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

    Route::get('/exams/{course}', [ExamController::class, 'show'])->name('exams.show');
    Route::post('/exams/{course}', [ExamController::class, 'submit'])->name('exams.submit');
    Route::get('/exams/result/{attempt}', [ExamController::class, 'result'])->name('exams.result');

    Route::get('/lessons/{lesson}', [LessonViewController::class, 'show'])->name('lessons.show');

    Route::get('/lessons/{lesson}/quiz', [LessonQuizController::class, 'show'])->name('lessons.quiz.show');
    Route::post('/lessons/{lesson}/quiz', [LessonQuizController::class, 'submit'])->name('lessons.quiz.submit');
    });

// --- Supervisor routes ---
Route::middleware(['auth', 'role:supervisor'])->prefix('supervisor')->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Supervisor/Dashboard');
    })->name('supervisor.dashboard');

    Route::get('/monitoring', [MonitoringController::class, 'index'])->name('supervisor.monitoring');
});

// --- Admin routes ---
Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
    
    Route::get('/students', [AdminStudentController::class, 'index'])->name('admin.students.index');
    Route::delete('/students/{student}', [AdminStudentController::class, 'destroy'])->name('admin.students.destroy');
    
    Route::get('/exam-settings', [AdminExamSettingController::class, 'index'])->name('admin.exam-settings.index');
    Route::put('/exam-settings/{course}', [AdminExamSettingController::class, 'update'])->name('admin.exam-settings.update');
    
    Route::get('/certificates', [CertificateLogController::class, 'index'])->name('admin.certificates.index');
    
    Route::get('/analytics', [AnalyticsController::class, 'index'])->name('admin.analytics.index');
    
    Route::get('/modules/{module}/lessons', [LessonController::class, 'index'])->name('admin.lessons.index');
    Route::post('/modules/{module}/lessons', [LessonController::class, 'store'])->name('admin.lessons.store');
    
    Route::get('/courses', [AdminCourseController::class, 'index'])->name('admin.courses.index');
    Route::post('/courses', [AdminCourseController::class, 'store'])->name('admin.courses.store');
    Route::delete('/courses/{course}', [AdminCourseController::class, 'destroy'])->name('admin.courses.destroy');

    Route::get('/courses/{course}/modules', [AdminModuleController::class, 'index'])->name('admin.modules.index');
    Route::post('/courses/{course}/modules', [AdminModuleController::class, 'store'])->name('admin.modules.store');
    Route::delete('/modules/{module}', [AdminModuleController::class, 'destroy'])->name('admin.modules.destroy');

    Route::delete('/lessons/{lesson}', [LessonController::class, 'destroy'])->name('admin.lessons.destroy');
    Route::post('/lessons/{lesson}/questions', [LessonController::class, 'storeQuestion'])->name('admin.lessons.questions.store');
    
    Route::delete('/questions/{question}', [LessonController::class, 'destroyQuestion'])->name('admin.lessons.questions.destroy');
    Route::post('/lessons/extract-pptx', [LessonController::class, 'extractPptx'])->name('admin.lessons.extract-pptx');
    
    Route::get('/staff', [StaffController::class, 'index'])->name('admin.staff.index');
    Route::post('/staff', [StaffController::class, 'store'])->name('admin.staff.store');
    Route::delete('/staff/{staff}', [StaffController::class, 'destroy'])->name('admin.staff.destroy');
    
    Route::post('/lessons/extract-pptx', [LessonController::class, 'extractPptx'])->name('admin.lessons.extract-pptx');
    Route::get('/lessons/{lesson}/preview', [LessonController::class, 'show'])->name('admin.lessons.preview');
    
    Route::get('/courses/{course}/exam-questions', [ExamQuestionController::class, 'index'])->name('admin.exam-questions.index');
    Route::post('/courses/{course}/exam-questions', [ExamQuestionController::class, 'store'])->name('admin.exam-questions.store');
    Route::delete('/exam-questions/{question}', [ExamQuestionController::class, 'destroy'])->name('admin.exam-questions.destroy');

    Route::put('/lessons/{lesson}', [LessonController::class, 'update'])->name('admin.lessons.update');
    });

// --- Public — no auth, matches the PDF's Certificate Verification screen ---
Route::get('/verify', function () {
    return Inertia::render('Verify/Index');
})->name('certificates.verify.form');

Route::get('/verify/{serial}', [CertificateController::class, 'verify'])->name('certificates.verify');

require __DIR__.'/auth.php';