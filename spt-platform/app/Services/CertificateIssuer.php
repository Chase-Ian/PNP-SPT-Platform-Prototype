<?php

namespace App\Services;

use App\Models\Certificate;
use App\Models\Course;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\SvgWriter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Issues certificates and renders their PDFs. Used when a trainee passes an
 * exam (ExamController) and when a certificate is viewed or downloaded
 * (CertificateController).
 */
class CertificateIssuer
{
    public function issue(User $user, Course $course): Certificate
    {
        // Archived certificates belong to an earlier version of the course, so a
        // trainee who redoes it after an archive gets a new one.
        $existing = Certificate::where('user_id', $user->id)->where('course_id', $course->id)
            ->whereNull('archived_at')->first();
        if ($existing) {
            return $existing;
        }

        return DB::transaction(function () use ($user, $course) {
            $issuedAt = now();

            // Insert first so the row's ID can number the certificate. IDs are
            // never reused, so deleting accounts (which deletes their
            // certificates) can't make two certificates share a number, the way
            // counting the remaining certificates did.
            $certificate = Certificate::create([
                'user_id' => $user->id,
                'course_id' => $course->id,
                'serial_id' => 'pending-'.Str::uuid(),
                // Must be unique. "user ID + course ID + second" wasn't: user 1 in
                // course 23 and user 12 in course 3 passing in the same second
                // produced the same hash and crashed the exam submission.
                'verification_hash' => hash('sha256', implode('|', [
                    $user->id, $course->id, $issuedAt->timestamp, Str::random(40),
                ])),
                'issued_at' => $issuedAt,
            ]);

            $year = $issuedAt->year;
            $serial = 'PNP-SPT-CP-'.$year.'-'.str_pad($certificate->id, 6, '0', STR_PAD_LEFT);

            // Training Control Number, e.g. SPT-PRON-2026-0001
            $region = strtoupper(substr(preg_replace('/\s+/', '', $user->region ?? 'REG'), 0, 4));
            $ctrlNo = 'SPT-'.$region.'-'.$year.'-'.str_pad($certificate->id, 4, '0', STR_PAD_LEFT);

            $certificate->update(['serial_id' => $serial, 'training_ctrl_no' => $ctrlNo]);

            $path = "certificates/{$serial}.pdf";
            Storage::put($path, $this->pdf($certificate)->output());
            $certificate->update(['pdf_path' => $path]);

            return $certificate;
        });
    }

    public function pdf(Certificate $certificate): \Barryvdh\DomPDF\PDF
    {
        $certificate->loadMissing(['user', 'course']);

        $user = $certificate->user;
        $course = $certificate->course;
        $verifyUrl = $certificate->verification_url;

        return Pdf::loadView('certificates.template', [
            'name' => $user->name,
            'course' => $course->title,
            'duration_hours' => $course->duration_hours ?? 3,
            'serial_id' => $certificate->serial_id,
            'training_ctrl_no' => $certificate->training_ctrl_no ?? 'N/A',
            'unit_office' => $user->unit_office ?? 'N/A',
            'region' => $user->region ?? '',
            'date' => $certificate->issued_at->format('d F Y'),
            'verify_url' => $verifyUrl,
            'qr_code_data_uri' => $this->qrDataUri($verifyUrl),
        ])->setPaper('a4', 'landscape');
    }

    /**
     * The QR code is drawn here on the server, as an SVG (no GD extension
     * needed). It used to be fetched from api.qrserver.com, which sent every
     * certificate's verification link to an outside service and fell back to
     * an unscannable placeholder whenever that service was unreachable.
     */
    public function qrDataUri(string $text): string
    {
        $qrCode = new QrCode(data: $text, size: 150, margin: 4);

        return (new SvgWriter())->write($qrCode)->getDataUri();
    }
}
