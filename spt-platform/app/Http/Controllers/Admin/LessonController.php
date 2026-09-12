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
            'lessons' => $module->lessons()->with('quizQuestions')->withCount('quizQuestions')->get(),
        ]);
    }

    public function store(Request $request, Module $module)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'duration_minutes' => 'required|integer|min:1',
            'blocks' => 'required|array|min:1',
            'blocks.*.type' => 'required|in:heading,paragraph,bullet_list,video,youtube',
        ]);

        $blocks = $this->resolveYoutubeBlocks($request->blocks);
        $this->validateBlockStructure($blocks);

        $module->lessons()->create([
            'course_id' => $module->course_id,
            'order' => $module->lessons()->max('order') + 1,
            'title' => $request->title,
            'content' => $blocks,
            'duration_minutes' => $request->duration_minutes,
        ]);

        return back();
    }

    public function destroy(Lesson $lesson)
    {
        $lesson->delete();
        return back();
    }

    public function updateQuestion(Request $request, LessonQuizQuestion $question)
    {
        $request->validate([
            'question' => 'required|string',
            'choices' => 'required|array|min:2',
            'correct_choice' => 'required|string',
        ]);

        $question->update($request->only('question', 'choices', 'correct_choice'));

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

    private function extractYoutubeId(string $url): ?string
    {
        if (preg_match('/(?:youtu\.be\/|youtube\.com\/(?:watch\?v=|embed\/))([a-zA-Z0-9_-]{11})/', $url, $matches)) {
            return $matches[1];
        }
        return null;
    }

    private function validateBlockStructure(array $blocks): void
    {
        $videoTypeIndexes = collect($blocks)->keys()
            ->filter(fn ($i) => in_array($blocks[$i]['type'], ['video', 'youtube']))
            ->values();

        abort_if($videoTypeIndexes->count() > 1, 422, 'Only one video (uploaded or YouTube) is allowed per lesson.');

        if ($videoTypeIndexes->count() === 1) {
            $index = $videoTypeIndexes->first();
            $isTop = $index === 0;
            $isBottom = $index === count($blocks) - 1;
            abort_unless($isTop || $isBottom, 422, 'The video block must be the first or last block in the lesson.');
        }
    }

    public function update(Request $request, Lesson $lesson)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'duration_minutes' => 'required|integer|min:1',
            'blocks' => 'required|array|min:1',
            'blocks.*.type' => 'required|in:heading,paragraph,bullet_list,video,youtube',
        ]);

        $blocks = $this->resolveYoutubeBlocks($request->blocks);
        $this->validateBlockStructure($blocks);

        $lesson->update([
            'title' => $request->title,
            'content' => $blocks,
            'duration_minutes' => $request->duration_minutes,
        ]);

        return back();
    }

    private function resolveYoutubeBlocks(array $blocks): array
    {
        return array_map(function ($block) {
            if ($block['type'] === 'youtube') {
                $id = $this->extractYoutubeId($block['url'] ?? '');
                abort_if(! $id, 422, 'That does not look like a valid YouTube URL.');
                $block['video_id'] = $id;
                unset($block['url']); // don't store the raw pasted URL, only the validated ID
            }
            return $block;
        }, $blocks);
    }

    public function previewQuiz(Lesson $lesson)
    {
        $questions = $lesson->quizQuestions()->get()->map(fn ($q) => [
            'id' => $q->id,
            'question' => $q->question,
            'choices' => $q->choices,
        ]);

        return Inertia::render('Admin/Lessons/QuizPreview', [
            'lesson' => $lesson->only('id', 'title', 'module_id'),
            'questions' => $questions,
        ]);
    }
    


}