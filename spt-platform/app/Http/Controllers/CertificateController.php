<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Services\CertificateIssuer;

class CertificateController extends Controller
{
    public function index(Request $request)
    {
        $certificates = $request->user()->certificates()
            ->with('course')
            ->latest('issued_at')
            ->get()
            ->map(fn ($cert) => [
                'id'               => $cert->id,
                'title'            => $cert->course->title,
                'instructor_name'  => $cert->course->instructor_name,
                'issued_at'        => $cert->issued_at->format('d F Y'),
                'serial_id'        => $cert->serial_id,
                'training_ctrl_no' => $cert->training_ctrl_no ?? 'N/A',
                'verification_url' => $cert->verification_url,
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

        $pdf = app(CertificateIssuer::class)->pdf($certificate);

        return $pdf->download("{$certificate->serial_id}.pdf");
    }

    public function verify(string $serial)
    {
        $certificate = Certificate::where('serial_id', $serial)
            ->with('user', 'course')
            ->first();

        return Inertia::render('Verify/Show', [
            'certificate' => $certificate ? [
                'serial_id'        => $certificate->serial_id,
                'training_ctrl_no' => $certificate->training_ctrl_no ?? 'N/A',
                'title'            => $certificate->course->title,
                'holder_name'      => $certificate->user->name,
                'unit_office'      => $certificate->user->unit_office ?? 'N/A',
                'region'           => $certificate->user->region ?? 'N/A',
                'issued_at'        => $certificate->issued_at->format('d F Y'),
                'verification_url' => $certificate->verification_url,
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

        $pdf = app(CertificateIssuer::class)->pdf($certificate);

        return $pdf->stream("{$certificate->serial_id}.pdf");
    }
}
