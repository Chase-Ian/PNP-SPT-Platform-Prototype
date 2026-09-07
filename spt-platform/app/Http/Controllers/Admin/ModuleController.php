<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Module;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ModuleController extends Controller
{
    public function index(Course $course)
    {
        return \Inertia\Inertia::render('Admin/Modules/Index', [
            'course' => $course->only('id', 'title'),
            'modules' => $course->modules,
        ]);
    }

    public function store(Request $request, Course $course)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'duration_minutes' => 'required|integer|min:1',
            'file' => 'required|file|max:51200',
        ]);

        $file = $request->file('file');
        $mime = $file->getMimeType();
        $allowed = [
            'application/pdf' => 'pdf',
            'video/mp4' => 'mp4',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'pptx',
        ];

        if (! array_key_exists($mime, $allowed)) {
            return back()->withErrors(['file' => 'Unsupported file type detected. Only PDF, PPTX, and MP4 files are allowed.'])->withInput();
        }

        $filename = Str::random(40) . '.' . $allowed[$mime];
        $path = $file->storeAs('modules', $filename);

        $course->modules()->create([
            'order' => $course->modules()->max('order') + 1,
            'title' => $request->title,
            'description' => $request->description,
            'file_path' => $path,
            'file_type' => $allowed[$mime],
            'duration_minutes' => $request->duration_minutes,
        ]);

        return back();
    }

    public function destroy(Module $module)
    {
        if ($module->file_path) {
            \Illuminate\Support\Facades\Storage::delete($module->file_path);
        }
        $module->delete();

        return back();
    }
}