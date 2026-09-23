<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;

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

        $pdf = $this->buildCertificatePdf($certificate);

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

        $pdf = $this->buildCertificatePdf($certificate);

        return $pdf->stream("{$certificate->serial_id}.pdf");
    }

    /**
     * Builds the DomPDF object for a given certificate, rendering
     * the full DICT-style template with QR code, trainee info, and all metadata.
     */
    private function buildCertificatePdf(Certificate $certificate): \Barryvdh\DomPDF\PDF
    {
        $certificate->loadMissing(['user', 'course']);

        $user      = $certificate->user;
        $course    = $certificate->course;
        $verifyUrl = $certificate->verification_url;
        $qrDataUri = $this->generateQrDataUri($verifyUrl);

        return Pdf::loadView('certificates.template', [
            'name'             => $user->name,
            'course'           => $course->title,
            'duration_hours'   => $course->duration_hours ?? 3,
            'serial_id'        => $certificate->serial_id,
            'training_ctrl_no' => $certificate->training_ctrl_no ?? 'N/A',
            'unit_office'      => $user->unit_office ?? 'N/A',
            'region'           => $user->region ?? '',
            'date'             => $certificate->issued_at->format('d F Y'),
            'verify_url'       => $verifyUrl,
            'qr_code_data_uri' => $qrDataUri,
        ])->setPaper('a4', 'landscape');
    }

    /**
     * Fetches a QR code image from an external API and returns it as a base64 data URI.
     * Falls back to an SVG placeholder if the request fails (e.g. offline environment).
     */
    private function generateQrDataUri(string $text): string
    {
        $encoded = urlencode($text);
        $apiUrl  = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={$encoded}&format=png&margin=4";

        try {
            $context = stream_context_create(['http' => ['timeout' => 5]]);
            $imgData = @file_get_contents($apiUrl, false, $context);
            if ($imgData !== false) {
                return 'data:image/png;base64,' . base64_encode($imgData);
            }
        } catch (\Throwable $e) {
            // fall through to SVG placeholder
        }

        // Offline placeholder — a simple SVG QR-like grid
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" viewBox="0 0 80 80">'
             . '<rect width="80" height="80" fill="white"/>'
             . '<rect x="5" y="5" width="25" height="25" fill="none" stroke="#000" stroke-width="3"/>'
             . '<rect x="10" y="10" width="15" height="15" fill="#000"/>'
             . '<rect x="50" y="5" width="25" height="25" fill="none" stroke="#000" stroke-width="3"/>'
             . '<rect x="55" y="10" width="15" height="15" fill="#000"/>'
             . '<rect x="5" y="50" width="25" height="25" fill="none" stroke="#000" stroke-width="3"/>'
             . '<rect x="10" y="55" width="15" height="15" fill="#000"/>'
             . '<text x="40" y="44" text-anchor="middle" font-size="5" fill="#000">SCAN</text>'
             . '</svg>';

        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }
}