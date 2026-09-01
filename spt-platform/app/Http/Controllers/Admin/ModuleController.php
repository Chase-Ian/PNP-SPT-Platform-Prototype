<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Module;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ModuleController extends Controller
{
    public function index()
    {
        $modules = Module::with('course')
            ->orderBy('course_id')
            ->orderBy('order')
            ->get()
            ->map(fn ($m) => [
                'id' => $m->id,
                'course_title' => $m->course->title,
                'order' => $m->order,
                'title' => $m->title,
                'description' => $m->description,
                'file_type' => $m->file_type,
                'file_path' => $m->file_path,
                'duration_minutes' => $m->duration_minutes,
            ]);

        $courses = Course::select('id', 'title')->get();

        return Inertia::render('Admin/Modules/Index', [
            'modules' => $modules,
            'courses' => $courses,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'course_id' => 'required|exists:courses,id',
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

        // Security backstop: verify the real MIME type server-side (finfo-based,
        // not the client-supplied extension). Defense-in-depth alongside
        // Laravel 11.44+'s fix for GHSA-78fx-h6xr-vch4. See docs/SECURITY_ADVISORIES.md.
        if (! array_key_exists($mime, $allowed)) {
            return back()->withErrors([
                'file' => 'Unsupported file type detected. Only PDF, PPTX, and MP4 files are allowed.',
            ])->withInput();
        }

        $path = $file->store('modules');

        Module::create([
            'course_id' => $request->course_id,
            'order' => Module::where('course_id', $request->course_id)->max('order') + 1,
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
            Storage::delete($module->file_path);
        }
        $module->delete();

        return back();
    }
}