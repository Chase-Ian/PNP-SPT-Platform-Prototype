<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\Lesson;
use App\Models\LessonQuizQuestion;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LessonController extends Controller
{
    public function index(Module $module)
    {
        return Inertia::render('Admin/Lessons/Index', [
            'module' => $module->only('id', 'title', 'course_id'),
            'lessons' => $module->lessons()->withCount('quizQuestions')->get(),
        ]);
    }

    public function store(Request $request, Module $module)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'duration_minutes' => 'required|integer|min:1',
            'blocks' => 'required|array|min:1',
            'blocks.*.type' => 'required|in:heading,paragraph,bullet_list,video',
        ]);

        $this->validateBlockStructure($request->blocks);

        $module->lessons()->create([
            'course_id' => $module->course_id,
            'order' => $module->lessons()->max('order') + 1,
            'title' => $request->title,
            'content' => $request->blocks,
            'duration_minutes' => $request->duration_minutes,
        ]);

        return back();
    }

    public function destroy(Lesson $lesson)
    {
        $lesson->delete();
        return back();
    }

    public function storeQuestion(Request $request, Lesson $lesson)
    {
        $request->validate([
            'question' => 'required|string',
            'choices' => 'required|array|min:2',
            'correct_choice' => 'required|string',
        ]);

        $lesson->quizQuestions()->create($request->only('question', 'choices', 'correct_choice'));

        return back();
    }

    public function destroyQuestion(LessonQuizQuestion $question)
    {
        $question->delete();
        return back();
    }

    public function show(Lesson $lesson)
    {
        return Inertia::render('Admin/Lessons/Preview', [
            'lesson' => $lesson,
        ]);
    }

    public function extractPptx(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:pptx|max:51200']);

        $blocks = (new \App\Services\PptxTextExtractor())->extract($request->file('file')->getRealPath());

        return response()->json(['blocks' => $blocks]);
    }

    private function validateBlockStructure(array $blocks): void
    {
        $videoIndexes = collect($blocks)->keys()->filter(fn ($i) => $blocks[$i]['type'] === 'video')->values();

        abort_if($videoIndexes->count() > 1, 422, 'Only one video block is allowed per lesson.');

        if ($videoIndexes->count() === 1) {
            $index = $videoIndexes->first();
            $isTop = $index === 0;
            $isBottom = $index === count($blocks) - 1;
            abort_unless($isTop || $isBottom, 422, 'The video block must be the first or last block in the lesson.');
        }
    }


}