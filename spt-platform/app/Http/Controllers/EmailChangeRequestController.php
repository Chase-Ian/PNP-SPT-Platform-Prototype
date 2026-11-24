<?php

namespace App\Http\Controllers;

use App\Models\EmailChangeRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Users can't edit their own email: it is the account's recovery route.
 * Instead they file a request that an administrator reviews.
 */
class EmailChangeRequestController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $request->merge([
            'new_email' => strtolower(preg_replace('/[\r\n]|%0[ad]/i', '', (string) $request->input('new_email'))),
        ]);

        $user = $request->user();

        $request->validate([
            'new_email' => [
                'required', 'string', 'email:filter', 'max:255',
                Rule::notIn([strtolower($user->email)]),
                Rule::unique(User::class, 'email'),
            ],
            'reason' => ['required', 'string', 'min:10', 'max:500'],
        ], [
            'new_email.not_in' => 'That is already your current email address.',
            'new_email.unique' => 'That email address is already used by another account.',
        ]);

        if ($user->emailChangeRequests()->pending()->exists()) {
            return back()->withErrors(['new_email' => 'You already have a pending request. Cancel it first to submit a new one.']);
        }

        $user->emailChangeRequests()->create([
            'current_email' => $user->email,
            'new_email' => $request->input('new_email'),
            'reason' => $request->input('reason'),
        ]);

        return back()->with('status', 'email-request-submitted');
    }

    public function destroy(Request $request, EmailChangeRequest $emailChangeRequest): RedirectResponse
    {
        abort_unless($emailChangeRequest->user_id === $request->user()->id, 403);
        abort_unless($emailChangeRequest->status === 'pending', 409);

        $emailChangeRequest->update(['status' => 'cancelled']);

        return back();
    }
}
