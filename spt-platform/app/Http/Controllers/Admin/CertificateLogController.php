<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\User;
use Inertia\Inertia;

class CertificateLogController extends Controller
{
    public function index()
    {
        $trainees = User::where('role', 'trainee')
            ->with(['certificates.course'])
            ->get();

        $grouped = $trainees->groupBy(fn ($u) => $u->unit_office ?? 'Unassigned')
            ->map(function ($users, $station) {
                return [
                    'station' => $station,
                    'officers' => $users->map(fn ($u) => [
                        'name' => $u->name,
                        'rank_or_role' => 'Patrol Officer', // no rank field yet — placeholder
                        'certificates' => $u->certificates->map(fn ($c) => [
                            'title' => $c->course->title,
                            'issued_at' => $c->issued_at->format('Y-m-d'),
                            'serial_id' => $c->serial_id,
                        ]),
                    ])->filter(fn ($u) => $u['certificates']->isNotEmpty())->values(),
                ];
            })
            ->filter(fn ($group) => $group['officers']->isNotEmpty())
            ->values();

        $totalCertificates = Certificate::count();
        $totalOfficers = $trainees->filter(fn ($u) => $u->certificates->isNotEmpty())->count();
        $totalStations = $grouped->count();

        return Inertia::render('Admin/Certificates/Index', [
            'stats' => [
                'monitoredUnits' => $totalStations,
                'traineePersonnel' => $totalOfficers,
                'totalIssued' => $totalCertificates,
                'verificationStatus' => $totalCertificates > 0 ? 100 : 0, // all seeded certs are valid; no "revoked" concept yet
            ],
            'stations' => $grouped,
        ]);
    }
}