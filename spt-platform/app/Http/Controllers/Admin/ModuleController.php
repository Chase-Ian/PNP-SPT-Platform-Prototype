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
            'file' => 'nullable|file|max:51200',
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
                return back()->withErrors(['file' => 'Unsupported file type detected. Only PDF, PPTX, and MP4 files are allowed.'])->withInput();
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
            'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'pptx',
        ];

        if (array_key_exists($mime, $allowed)) {
            return $allowed[$mime];
        }

        // Fallback: some PPTX files get misdetected as generic binary/zip data
        // on Windows. Verify by checking for the OOXML presentation marker file
        // inside the ZIP structure rather than trusting finfo's MIME guess.
        if (in_array($mime, ['application/octet-stream', 'application/zip']) && $this->isRealPptx($file->getRealPath())) {
            return 'pptx';
        }

        return null;
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