<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Module;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
            'file' => 'nullable|file|mimes:pdf,mp4|max:51200', // mimes rule makes custom detection optional
        ]);

        $data = [
            'order' => $course->modules()->max('order') + 1,
            'title' => $request->title,
            'description' => $request->description,
            'duration_minutes' => $request->duration_minutes,
        ];

        if ($request->hasFile('file')) {
            $fileType = $this->detectFileType($request->file('file'));

            if (! $fileType) {
                return back()->withErrors(['file' => 'Unsupported file type detected. Only PDF, PPTX, and MP4 files are allowed.'])->withInput();
            }

            $filename = Str::random(40) . '.' . $fileType;
            $data['file_path'] = $request->file('file')->storeAs('modules', $filename);
            $data['file_type'] = $fileType;
        }

        $course->modules()->create($data);

        return back();
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
            $fileType = $this->detectFileType($request->file('file'));

            if (! $fileType) {
                return back()->withErrors(['file' => 'Unsupported file type detected. Only PDF, and MP4 files are allowed.'])->withInput();
            }

            if ($module->file_path) {
                Storage::delete($module->file_path);
            }

            $filename = Str::random(40) . '.' . $fileType;
            $data['file_path'] = $request->file('file')->storeAs('modules', $filename);
            $data['file_type'] = $fileType;
        }

        $module->update($data);

        return back();
}


     // Make store() require the file optionally too, matching update()'s behavior
    private function detectFileType($file): ?string
    {
        $mime = $file->getMimeType();

        $allowed = [
            'application/pdf' => 'pdf',
            'video/mp4' => 'mp4',
        ];

        return $allowed[$mime] ?? null;
    }

    private function isRealPptx(string $path): bool
    {
        $zip = new \ZipArchive();
        if ($zip->open($path) !== true) {
            return false;
        }

        $hasMarker = $zip->locateName('ppt/presentation.xml') !== false;
        $zip->close();

        return $hasMarker;
    }

    public function viewFile(Module $module)
    {
        // 1. Verify file exists on S3
        abort_unless($module->file_path && Storage::exists($module->file_path), 404, 'No file attached to this module.');

        // 2. Determine correct MIME type
        $mime = match ($module->file_type) {
            'mp4'  => 'video/mp4',
            'pdf'  => 'application/pdf',
            default => Storage::mimeType($module->file_path) ?? 'application/pdf',
        };

        // 3. Stream file directly from AWS S3 using inline disposition
        return Storage::response($module->file_path, null, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="' . basename($module->file_path) . '"',
        ]);
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