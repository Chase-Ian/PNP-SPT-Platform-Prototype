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
use App\Http\Controllers\Supervisor\AnalyticsController;
use App\Http\Controllers\Supervisor\MonitoringController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\Admin\LessonController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Admin\CourseController as AdminCourseController;
use App\Http\Controllers\Admin\ExamQuestionController;
use App\Http\Controllers\LessonViewController;
use App\Http\Controllers\LessonQuizController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Supervisor\DashboardController as SupervisorDashboardController;
use App\Http\Controllers\Admin\TwoFactorResetController;
use App\Http\Controllers\LegalController;

Route::get('/', function () {
    return redirect('/login');
});

Route::get('/dashboard', function () {
    $user = auth()->user();

    // Each account has one role, so staff always go to their own dashboard.
    if ($user->role === 'admin') {
        return redirect()->route('admin.dashboard');
    }
    if ($user->role === 'supervisor') {
        return redirect()->route('supervisor.dashboard');
    }

    return app(DashboardController::class)->index(request());
})->middleware(['auth', 'verified'])->name('dashboard');

// --- Every signed-in account: profile, notifications, lesson pictures ---
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markRead'])->name('notifications.read');

    // Trainees see these pictures in lessons; staff in the lesson editor and previews.
    Route::get('/lesson-images/{filename}', [LessonController::class, 'showImage'])
        ->where('filename', '[0-9a-f\-]{36}\.(jpg|png)')->name('lessons.images.show');
});

// --- Training: trainees only. Each account has exactly one role, so
// supervisors and admins can't enroll, take lessons or exams, or hold
// certificates. ---
Route::middleware(['auth', 'role:trainee'])->group(function () {
    Route::get('/courses', [CourseController::class, 'index'])->name('courses.index');
    Route::post('/courses/{course}/enroll', [CourseController::class, 'enroll'])->name('courses.enroll');
    Route::post('/courses/{course}/drop', [CourseController::class, 'drop'])->name('courses.drop');

    Route::get('/certificates', [CertificateController::class, 'index'])->name('certificates.index');
    Route::get('/certificates/{certificate}/download', [CertificateController::class, 'download'])->name('certificates.download');
    Route::get('/certificates/{certificate}/view', [CertificateController::class, 'view'])->name('certificates.view');

    Route::get('/exams/{course}', [ExamController::class, 'show'])->name('exams.show');
    Route::post('/exams/{course}', [ExamController::class, 'submit'])->name('exams.submit');
    Route::get('/exams/result/{attempt}', [ExamController::class, 'result'])->name('exams.result');

    Route::get('/lessons/{lesson}', [LessonViewController::class, 'show'])->name('lessons.show');
    Route::get('/lessons/{lesson}/quiz', [LessonQuizController::class, 'show'])->name('lessons.quiz.show');
    Route::post('/lessons/{lesson}/quiz', [LessonQuizController::class, 'submit'])->name('lessons.quiz.submit');
});

// --- Supervisor routes ---
Route::middleware(['auth', 'role:supervisor'])->prefix('supervisor')->group(function () {
    Route::get('/dashboard', [SupervisorDashboardController::class, 'index'])->name('supervisor.dashboard');

    // Trainee monitoring belongs to supervisors (statuses from saved records only).
    Route::get('/trainees', [MonitoringController::class, 'index'])->name('supervisor.trainees.index');
    Route::get('/trainees/{student}', [MonitoringController::class, 'show'])->name('supervisor.trainees.show');
    Route::post('/courses/{course}/archive-results', [MonitoringController::class, 'archive'])->name('supervisor.courses.archive-results');
    Route::get('/analytics', [AnalyticsController::class, 'index'])->name('supervisor.analytics.index');
});

// --- Admin routes ---
Route::middleware(['auth', 'role:admin,supervisor'])->prefix('admin')->group(function () {
    Route::get('/courses', [AdminCourseController::class, 'index'])->name('admin.courses.index');
    Route::post('/courses', [AdminCourseController::class, 'store'])->name('admin.courses.store');
    Route::put('/courses/{course}', [AdminCourseController::class, 'update'])->name('admin.courses.update');
    Route::delete('/courses/{course}', [AdminCourseController::class, 'destroy'])->name('admin.courses.destroy');

    Route::get('/courses/{course}/modules', [AdminModuleController::class, 'index'])->name('admin.modules.index');
    Route::post('/courses/{course}/modules', [AdminModuleController::class, 'store'])->name('admin.modules.store');
    Route::post('/modules/{module}', [AdminModuleController::class, 'update'])->name('admin.modules.update');
    Route::delete('/modules/{module}', [AdminModuleController::class, 'destroy'])->name('admin.modules.destroy');
    Route::get('/modules/{module}/view-file', [AdminModuleController::class, 'viewFile'])->name('admin.modules.view-file');

    Route::get('/modules/{module}/lessons', [LessonController::class, 'index'])->name('admin.lessons.index');
    Route::post('/modules/{module}/lessons', [LessonController::class, 'store'])->name('admin.lessons.store');
    Route::put('/lessons/{lesson}', [LessonController::class, 'update'])->name('admin.lessons.update');
    Route::delete('/lessons/{lesson}', [LessonController::class, 'destroy'])->name('admin.lessons.destroy');
    Route::post('/lessons/{lesson}/questions', [LessonController::class, 'storeQuestion'])->name('admin.lessons.questions.store');
    Route::put('/questions/{question}', [LessonController::class, 'updateQuestion'])->name('admin.lessons.questions.update');
    Route::delete('/questions/{question}', [LessonController::class, 'destroyQuestion'])->name('admin.lessons.questions.destroy');
    Route::post('/lessons/extract-pptx', [LessonController::class, 'extractPptx'])
    ->middleware('throttle:10,1')->name('admin.lessons.extract-pptx');
    Route::post('/lessons/upload-image', [LessonController::class, 'uploadImage'])
    ->middleware('throttle:30,1')->name('admin.lessons.upload-image');
    Route::get('/lessons/{lesson}/preview', [LessonController::class, 'show'])->name('admin.lessons.preview');
    Route::get('/lessons/{lesson}/quiz-preview', [LessonController::class, 'previewQuiz'])->name('admin.lessons.quiz-preview');

    Route::get('/courses/{course}/exam-questions', [ExamQuestionController::class, 'index'])->name('admin.exam-questions.index');
    Route::post('/courses/{course}/exam-questions', [ExamQuestionController::class, 'store'])->name('admin.exam-questions.store');
    Route::put('/exam-questions/{question}', [ExamQuestionController::class, 'update'])->name('admin.exam-questions.update');
    Route::delete('/exam-questions/{question}', [ExamQuestionController::class, 'destroy'])->name('admin.exam-questions.destroy');
    Route::put('/courses/{course}/exam-settings', [ExamQuestionController::class, 'updateSettings'])->name('admin.exam-settings.settings.update');
    Route::get('/courses/{course}/exam-questions/preview', [ExamQuestionController::class, 'preview'])->name('admin.exam-questions.preview');

});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/students', [StudentController::class, 'index'])->name('admin.students.index');
    Route::post('/students/{student}/toggle-lock', [StudentController::class, 'toggleLock'])->name('admin.students.toggle-lock');
    Route::delete('/students/{student}', [StudentController::class, 'destroy'])->name('admin.students.destroy');
    Route::post('/students/{student}/reinstate', [StudentController::class, 'reinstate'])->withTrashed()->name('admin.students.reinstate');
    Route::delete('/students/{student}/permanently', [StudentController::class, 'erase'])->withTrashed()->name('admin.students.erase');
    Route::get('/certificates', [CertificateLogController::class, 'index'])->name('admin.certificates.index');
    Route::get('/staff', [StaffController::class, 'index'])->name('admin.staff.index');
    Route::post('/staff', [StaffController::class, 'store'])->name('admin.staff.store');
    Route::post('/staff/{staff}/toggle-lock', [StaffController::class, 'toggleLock'])->name('admin.staff.toggle-lock');
    Route::delete('/staff/{staff}', [StaffController::class, 'destroy'])->name('admin.staff.destroy');
    Route::post('/staff/{staff}/reinstate', [StaffController::class, 'reinstate'])->withTrashed()->name('admin.staff.reinstate');
    Route::delete('/staff/{staff}/permanently', [StaffController::class, 'erase'])->withTrashed()->name('admin.staff.erase');

    // Account recovery
    Route::post('/users/{user}/reset-two-factor', [TwoFactorResetController::class, 'store'])->name('admin.users.reset-two-factor');
});

// --- Public legal documents (linked from sign-up and every page footer) ---
Route::get('/terms', [LegalController::class, 'terms'])->name('legal.terms');
Route::get('/privacy', [LegalController::class, 'privacy'])->name('legal.privacy');

// Accepting the current version (the Access Gate sends users here when needed)
Route::middleware('auth')->group(function () {
    Route::get('/legal/accept', [LegalController::class, 'showAccept'])->name('legal.accept');
    Route::post('/legal/accept', [LegalController::class, 'accept'])->name('legal.accept.store');
});

// --- Public — no auth, matches the PDF's Certificate Verification screen ---
Route::get('/verify', function () {
    return Inertia::render('Verify/Index');
})->name('certificates.verify.form');

// Limited per visitor, so the holders' names can't be harvested by stepping
// through certificate numbers.
Route::get('/verify/{serial}', [CertificateController::class, 'verify'])
    ->middleware('throttle:30,1')->name('certificates.verify');

require __DIR__.'/auth.php';