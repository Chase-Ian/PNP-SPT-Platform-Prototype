<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class CertificateController extends Controller
{
    public function index(Request $request)
    {
        $certificates = $request->user()->certificates()
            ->with('course')
            ->latest('issued_at')
            ->get()
            ->map(fn ($cert) => [
                'id' => $cert->id,
                'title' => $cert->course->title,
                'instructor_name' => $cert->course->instructor_name,
                'issued_at' => $cert->issued_at->format('Y-m-d'),
                'serial_id' => $cert->serial_id,
            ]);

        return Inertia::render('Certificates/Index', [
            'certificates' => $certificates,
        ]);
    }

    public function download(Request $request, Certificate $certificate)
    {
        abort_unless($certificate->user_id === $request->user()->id, 403);

        // Manual expiry check — do NOT replace with Storage::temporaryUrl()
        // or URL::temporarySignedRoute(). See docs/SECURITY_ADVISORIES.md
        // (GHSA-crmm-hgp2-wgrp — signed URL path confusion, unpatched in 11.x)
        abort_if(
            $certificate->issued_at->addDays(90)->isPast(),
            403,
            'This certificate link has expired. Please contact your training administrator.'
        );

        if (! $certificate->pdf_path || ! Storage::exists($certificate->pdf_path)) {
            abort(404, 'Certificate file not found.');
        }

        return Storage::download($certificate->pdf_path, "{$certificate->serial_id}.pdf");
    }

    public function verify(string $serial)
    {
        $certificate = Certificate::where('serial_id', $serial)
            ->with('user', 'course')
            ->first();

        return Inertia::render('Verify/Show', [
            'certificate' => $certificate ? [
                'serial_id' => $certificate->serial_id,
                'title' => $certificate->course->title,
                'holder_name' => $certificate->user->name,
                'issued_at' => $certificate->issued_at->format('Y-m-d'),
            ] : null,
        ]);
    }

    public function view(Request $request, Certificate $certificate)
    {
        abort_unless($certificate->user_id === $request->user()->id, 403);

        abort_if(
            $certificate->issued_at->addDays(90)->isPast(),
            403,
            'This certificate link has expired. Please contact your training administrator.'
        );

        if (! $certificate->pdf_path || ! Storage::exists($certificate->pdf_path)) {
            abort(404, 'Certificate file not found.');
        }

        // Inline display instead of forcing a download
        $file = Storage::get($certificate->pdf_path);

        return response($file, 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="' . $certificate->serial_id . '.pdf"');
    }
}