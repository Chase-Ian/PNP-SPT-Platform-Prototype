<?php

namespace App\Http\Controllers;

use App\Services\LegalAgreement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LegalController extends Controller
{
    public function __construct(private LegalAgreement $legal) {}

    /** Public: readable before signing up and from every page footer. */
    public function terms(): Response
    {
        return Inertia::render('Legal/Terms', [
            ...$this->documentProps(),
            'deletedAccountDays' => (int) config('security.deleted_account_days', 30),
        ]);
    }

    public function privacy(): Response
    {
        return Inertia::render('Legal/Privacy', [
            ...$this->documentProps(),
            'processors' => config('legal.processors'),
            'twoFactorScope' => config('security.require_2fa'),
            'deletedAccountDays' => (int) config('security.deleted_account_days', 30),
        ]);
    }

    /**
     * Shown by the Access Gate to signed-in users who haven't accepted the
     * current version (accounts created by an Admin, accounts that existed
     * before these documents, and everyone after a version change).
     */
    public function showAccept(Request $request): Response|RedirectResponse
    {
        $user = $request->user();

        if ($this->legal->hasAccepted($user)) {
            return redirect()->route('dashboard');
        }

        return Inertia::render('Legal/Accept', [
            'version' => $this->legal->currentVersion(),
            'effectiveDate' => config('legal.effective_date'),
            'isUpdate' => $user->terms_accepted_version !== null,
        ]);
    }

    public function accept(Request $request): RedirectResponse
    {
        $request->validate(['accepted' => ['accepted']], [
            'accepted.accepted' => 'Please confirm that you agree to the Terms of Service and have read the Privacy Notice.',
        ]);

        $this->legal->record($request->user(), $request);

        // /dashboard sends admins and supervisors on to their own dashboards.
        return redirect()->intended(route('dashboard'));
    }

    private function documentProps(): array
    {
        return [
            'version' => $this->legal->currentVersion(),
            'effectiveDate' => config('legal.effective_date'),
            'draft' => (bool) config('legal.draft'),
            'controller' => config('legal.controller'),
            'dpo' => config('legal.dpo'),
            'aiAssistant' => (bool) config('legal.ai_assistant'),
        ];
    }
}
