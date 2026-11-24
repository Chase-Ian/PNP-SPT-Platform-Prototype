<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailChangeRequest;
use App\Models\User;
use App\Notifications\EmailAddressChanged;
use App\Notifications\EmailChangeDecision;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Inertia\Inertia;
use Inertia\Response;

class EmailChangeRequestController extends Controller
{
    public function index(): Response
    {
        $format = fn (EmailChangeRequest $r) => [
            'id' => $r->id,
            'user' => $r->user ? [
                'id' => $r->user->id,
                'name' => trim("{$r->user->rank} {$r->user->name}"),
                'role' => $r->user->role,
                'unit_office' => $r->user->unit_office,
            ] : null,
            'current_email' => $r->current_email,
            'new_email' => $r->new_email,
            'reason' => $r->reason,
            'status' => $r->status,
            'admin_note' => $r->admin_note,
            'reviewer' => $r->reviewer?->name,
            'created_at' => $r->created_at->format('Y-m-d H:i'),
            'reviewed_at' => $r->reviewed_at?->format('Y-m-d H:i'),
        ];

        return Inertia::render('Admin/EmailRequests/Index', [
            'pending' => EmailChangeRequest::pending()->with('user')->oldest()->get()->map($format),
            'recent' => EmailChangeRequest::whereIn('status', ['approved', 'rejected'])
                ->with(['user', 'reviewer'])->latest('reviewed_at')->limit(25)->get()->map($format),
        ]);
    }

    public function approve(Request $request, EmailChangeRequest $emailChangeRequest): RedirectResponse
    {
        $r = $emailChangeRequest;
        abort_unless($r->status === 'pending', 409);
        abort_if($r->user_id === $request->user()->id, 403, 'Another administrator must review your own request.');

        $request->validate(['admin_note' => ['nullable', 'string', 'max:500']]);

        if (User::where('email', $r->new_email)->where('id', '!=', $r->user_id)->exists()) {
            return back()->withErrors(['request' => "{$r->new_email} is now used by another account. Reject this request instead."]);
        }

        $user = $r->user;
        $oldEmail = $user->email;

        DB::transaction(function () use ($r, $user, $request) {
            // The new address has to be verified before the account can be used again.
            $user->forceFill(['email' => $r->new_email, 'email_verified_at' => null])->save();

            $r->update([
                'status' => 'approved',
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
                'admin_note' => $request->input('admin_note'),
            ]);
        });

        Log::warning('Email changed by admin approval', [
            'user_id' => $user->id, 'admin_id' => $request->user()->id, 'request_id' => $r->id,
        ]);

        $user->sendEmailVerificationNotification();
        Notification::route('mail', $oldEmail)->notify(new EmailAddressChanged($user->first_name, $r->new_email));
        $user->notify(new EmailChangeDecision($r));

        return back()->with('status', 'email-request-approved');
    }

    public function reject(Request $request, EmailChangeRequest $emailChangeRequest): RedirectResponse
    {
        $r = $emailChangeRequest;
        abort_unless($r->status === 'pending', 409);
        abort_if($r->user_id === $request->user()->id, 403, 'Another administrator must review your own request.');

        $request->validate(['admin_note' => ['required', 'string', 'max:500']], [
            'admin_note.required' => 'Give the requester a reason for the rejection.',
        ]);

        $r->update([
            'status' => 'rejected',
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
            'admin_note' => $request->input('admin_note'),
        ]);

        $r->user?->notify(new EmailChangeDecision($r));

        return back()->with('status', 'email-request-rejected');
    }
}
