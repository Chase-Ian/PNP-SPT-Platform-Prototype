<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\LessonProgress;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LessonViewController extends Controller
{
    public function show(Request $request, Lesson $lesson)
    {
        $user = $request->user();
        $module = $lesson->module;
        $allLessons = $module->lessons; // ordered by 'order', via the model relationship

        // Guard: must be enrolled in the course this lesson belongs to
        $enrolled = $user->enrollments()->where('course_id', $lesson->course_id)->exists();
        abort_unless($enrolled, 403, 'You must enroll in this course before viewing lessons.');

        $progressMap = LessonProgress::where('user_id', $user->id)
            ->whereIn('lesson_id', $allLessons->pluck('id'))
            ->get()
            ->keyBy('lesson_id');

        // Determine lock state: a lesson is unlocked if it's the first one,
        // or the previous lesson's quiz has been passed
        $sidebar = $allLessons->values()->map(function ($l, $index) use ($allLessons, $progressMap) {
            $passed = $progressMap->get($l->id)?->quiz_passed ?? false;
            $previousPassed = $index === 0 || ($progressMap->get($allLessons[$index - 1]->id)?->quiz_passed ?? false);

            return [
                'id' => $l->id,
                'title' => $l->title,
                'passed' => $passed,
                'unlocked' => $previousPassed,
            ];
        });

        $currentIndex = $allLessons->search(fn ($l) => $l->id === $lesson->id);
        $isUnlocked = $sidebar[$currentIndex]['unlocked'];
        abort_unless($isUnlocked, 403, 'Complete the previous lesson\'s quiz to unlock this one.');

        $totalLessons = $allLessons->count();
        $completedCount = $sidebar->where('passed', true)->count();

        return Inertia::render('Lessons/Show', [
            'module' => ['id' => $module->id, 'title' => $module->title],
            'lesson' => $lesson,
            'sidebar' => $sidebar,
            'currentIndex' => $currentIndex,
            'progressPercent' => $totalLessons > 0 ? round(($completedCount / $totalLessons) * 100) : 0,
            'nextLesson' => $allLessons->get($currentIndex + 1)?->id,
            'previousLesson' => $currentIndex > 0 ? $allLessons->get($currentIndex - 1)->id : null,
        ]);
    }
}