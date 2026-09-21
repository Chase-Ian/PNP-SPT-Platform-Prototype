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

        public function update(Request $request, Module $module)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'duration_minutes' => 'required|integer|min:1',
            'file' => 'nullable|file|max:51200',
        ]);

        $data = $request->only('title', 'description', 'duration_minutes');

        if ($request->hasFile('file')) {
            $mime = $request->file('file')->getMimeType();
            $allowed = [
                'application/pdf' => 'pdf',
                'video/mp4' => 'mp4',
                'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'pptx',
            ];

            if (! array_key_exists($mime, $allowed)) {
                return back()->withErrors(['file' => 'Unsupported file type detected. Only PDF, PPTX, and MP4 files are allowed.'])->withInput();
            }

            // Replace the old file
            if ($module->file_path) {
                Storage::delete($module->file_path);
            }

            $filename = Str::random(40) . '.' . $allowed[$mime];
            $data['file_path'] = $request->file('file')->storeAs('modules', $filename);
            $data['file_type'] = $allowed[$mime];
        }

        $module->update($data);

        return back();
    }

     // Make store() require the file optionally too, matching update()'s behavior
    public function store(Request $request, Course $course)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'duration_minutes' => 'required|integer|min:1',
            'file' => 'nullable|file|max:51200', // now optional
        ]);

        $data = [
            'order' => $course->modules()->max('order') + 1,
            'title' => $request->title,
            'description' => $request->description,
            'duration_minutes' => $request->duration_minutes,
        ];

        if ($request->hasFile('file')) {
            $mime = $request->file('file')->getMimeType();
            $allowed = [
                'application/pdf' => 'pdf',
                'video/mp4' => 'mp4',
                'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'pptx',
            ];

            if (! array_key_exists($mime, $allowed)) {
                return back()->withErrors(['file' => 'Unsupported file type detected. Only PDF, PPTX, and MP4 files are allowed.'])->withInput();
            }

            $filename = Str::random(40) . '.' . $allowed[$mime];
            $data['file_path'] = $request->file('file')->storeAs('modules', $filename);
            $data['file_type'] = $allowed[$mime];
        }

        $course->modules()->create($data);

        return back();
    }

        // New: view the uploaded file
    public function viewFile(Module $module)
    {
        abort_unless($module->file_path && Storage::exists($module->file_path), 404, 'No file attached to this module.');

        $file = Storage::get($module->file_path);
        $mime = match ($module->file_type) {
            'pdf' => 'application/pdf',
            'mp4' => 'video/mp4',
            default => 'application/octet-stream',
        };

        // PPTX can't be viewed inline by browsers — force download instead
        $disposition = $module->file_type === 'pptx' ? 'attachment' : 'inline';

        return response($file, 200)
            ->header('Content-Type', $mime)
            ->header('Content-Disposition', "{$disposition}; filename=\"" . basename($module->file_path) . '"');
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