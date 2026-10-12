# SPT-Platform — Technical Documentation

*Supersedes the Oct 2, 2026 version. Sections 1–8 below are carried over
unchanged from that version except where marked **(updated)**. New
material is in Section 9.*

## 1. Architecture Overview

Email delivery: Brevo SMTP relay (smtp-relay.brevo.com:587), free tier
(300 emails/day). Chosen over Resend because Resend's sandbox mode only
delivers to individually pre-verified recipient addresses. Known
deliverability caveat: sender verification was done against a
school-owned address, so mail lacks SPF/DKIM alignment for that domain —
Gmail-hosted recipients work, the school's own mail infrastructure does
not reliably receive it. Durable fix (still not done): verify a domain
the project/PNP ITMS actually controls and add Brevo's SPF/DKIM records.

## 2. Role & Access Model

The Supervisor content-management mismatch (course/module/lesson/exam
routes previously Admin-only, per the user stories should be
Admin+Supervisor) was fixed by widening route middleware to
`role:admin,supervisor` and adding an `AdminOrSupervisorLayout.vue`
wrapper that picks the right sidebar at render time — no controller or
page forking needed. Roster/staff/certificates-log/analytics stay
`role:admin` only. Supervisor's dashboard absorbed the old Monitoring
page (trainee table lives directly on `Supervisor/Dashboard.vue`); the
old `Supervisor/Monitoring.vue` is unused but left in place.

## 3. Role-Aware Redirect Fix

`/dashboard` itself is now role-aware (computed on every load, not just
at login), with a `?view=officer` escape hatch so "Switch to Officer
View" can round-trip back via a "← Back to Admin/Supervisor View" link.

## 4. Module File Upload/View

Two bugs fixed: a missing `Storage` facade import (namespace resolution
failure), and `response()->file()` assuming local disk instead of
streaming via the disk-agnostic `Storage::response()` (broke on S3).
PPTX support was dropped from module file uploads — browsers can't
render it inline — so uploads now accept `.pdf`/`.mp4` only. The
separate lesson-block PPTX **text-extraction** import path is unaffected.

## 5. Admin Content-Management Pages

Courses and Modules gained edit (not just create/delete), matching
Lessons' existing dual-purpose-form pattern. Module file upload is now
optional on both create and edit. A "View File" action was added per
module row.

## 6. PPTX MIME-Detection Fallback

The ZIP-marker fallback check (for Windows `finfo` misreporting PPTX as
`application/octet-stream`) was accidentally dropped during the Section
5 rewrite and re-added as a shared `detectFileType()`/`isRealPptx()`
pair. **(updated — see Section 9.1: in the current code this pair only
guards the lesson PPTX import path in practice; `isRealPptx()` is dead
code inside `ModuleController` now that module uploads no longer accept
PPTX at all, and the lesson import endpoint's own `extractPptx()` does
not currently call it either — see 9.1 for detail.)**

## 7. Password Reset Email Customization

Breeze's `ResetPassword` notification is overridden via
`ResetPassword::toMailUsing()` in `AppServiceProvider::boot()` for
subject/greeting/body copy (confirmed still in place, using
`first_name`). Two production requirements flagged as unverified at the
time: `APP_URL` must be the real Render domain, and `APP_NAME` must not
be the Laravel scaffold default.

---

## 9. Changes Since the Oct 2 Version

### 9.1 Analytics route was dead-wired — now fixed

`AnalyticsController` (region compliance, course pass-rates, live stat
counts) existed fully built, but `routes/web.php` still pointed
`/admin/analytics` at a bare closure rendering the page with **no
props**. Since `Admin/Analytics/Index.vue` reads `stats.totalEnrolled`
unconditionally, this would throw on load, not just show stale/static
numbers as previously documented. **Fixed in this pass** — the route now
calls `AnalyticsController@index`. *(Patch applied to a local read-only
clone of the repo; apply the one-line route change yourself if you
haven't pulled it — diff below.)*

```diff
- use App\Http\Controllers\Admin\AnalyticsController;
+ use App\Http\Controllers\Admin\AnalyticsController as AdminAnalyticsController;
  ...
- Route::get('/analytics', fn () => Inertia::render('Admin/Analytics/Index'))->name('admin.analytics.index');
+ Route::get('/analytics', [AdminAnalyticsController::class, 'index'])->name('admin.analytics.index');
```

This also means "Real Analytics historical tracking — Not built" in the
open-items table was overstated: live per-request stats (enrollment
counts, pass rates, regional compliance) **are** computed from the
database now. What's still genuinely missing is *historical/trend*
tracking (deltas over time) — the controller always computes
current-moment values, nothing is snapshotted over time.

### 9.2 Region-validation placeholder bug — confirmed resolved everywhere

The Oct 2 doc listed this as "still needs auditing in `StaffController`
and `ProfileUpdateRequest`." Checked both directly: both already contain
the real 17-item PRO region list (no leftover `/* PRO list, unchanged */`
placeholder). This item can be dropped from the open-items table.

### 9.3 Two-Factor Authentication (Fortify) — entirely undocumented until now

The repo's most recent commit is titled "2fa final patch," and
`laravel/fortify` (^1.40) is installed, but no prior version of this
document mentions it. For the record, confirmed live in the current
codebase:

- Only `Features::twoFactorAuthentication(['confirmPassword' => true, 'window' => 1])`
  is enabled in `config/fortify.php` — registration, password reset,
  email verification, and profile/password updates are all left
  disabled so Breeze's own controllers stay authoritative.
- `User` uses Fortify's `TwoFactorAuthenticatable` trait (not added to
  `$fillable` — Fortify manages those columns internally).
- Login flow: `AuthenticatedSessionController::store()` validates
  credentials, checks `is_locked`, then if 2FA is confirmed, stashes
  `login.id`/`login.remember` in session and redirects to a custom
  `/two-factor-challenge` route (outside `auth` middleware).
- `TwoFactorChallengeController::store()` is correctly implemented using
  Fortify's own `Laravel\Fortify\Http\Requests\TwoFactorLoginRequest`
  (`hasChallengedUser()`, `hasValidCode()`, `validRecoveryCode()`,
  `challengedUser()`) rather than reaching into the model trait's
  protected `twoFactorAuthenticationSecret()` method directly — the
  latter throws `BadMethodCallException` via Eloquent's
  `__call`/`forwardCallTo`, which was the original production bug this
  design avoids.
- `Fortify::twoFactorChallengeView()` is bound to render a custom Inertia
  page (`Auth/TwoFactorChallenge.vue`) instead of Fortify's default
  Blade view. A password-confirmation modal in
  `Profile/Partials/TwoFactorForm.vue` handles Fortify's 423 responses
  on enable/disable.

**Action:** this deserves its own permanent section in the main
document (not just this changelog) — it's a complete, working feature
area with no prior writeup at all.

### 9.4 QR code on certificates — now built (was "Not built")

`CertificateController` and `ExamController` both generate a QR code
(`generateQrDataUri()`) encoding the certificate's verification URL,
embedded as a data URI in the certificate PDF/view, with an offline SVG
placeholder fallback if the external QR API call fails. Implementation
calls `api.qrserver.com`'s free image API rather than the
`endroid/qr-code` package already present in `composer.json` — that
package is installed but currently unused; worth deciding whether to
switch to it for an offline-capable generator (no external API
dependency) before handover, since PNP's "own localized infrastructure"
requirement (US4.4) is already flagged as a concern elsewhere and an
external QR API call is one more outbound dependency in that light.

The Verify pages (`Verify/Index.vue`, `Verify/Show.vue`,
`Certificates/Index.vue`) now reference the QR code with Lucide
`QrCode` icons and copy explaining "scan the QR code or visit the
verify link."

### 9.5 QR-*scanning* verification — still not built, clarified

Because the QR now encodes a plain URL, any phone's native camera app
can already open the verification page by scanning it — so the
*physical workflow* US4.2 describes mostly works today. What's still
missing is an **in-app** scanner (no camera/`getUserMedia`/barcode
library found in `resources/js/Pages/Verify/`) for someone verifying
from a desktop or a non-camera device. Downgrading this from "not
started" to "partially covered, in-app scanner still open" is more
accurate.

### 9.6 Everything else checked and unchanged

Confirmed still true, no action needed to this document:
- AI Learning Assistant (Epic 3) — not built, no related files found.
- Module-level filtering on Supervisor dashboard — still name/unit-office
  search only (`Supervisor/Dashboard.vue`).
- Server-side exam timer enforcement — `ExamController` still has no
  `started_at`-vs-`now()` check on submit; timer remains client-side only.
- Forced password reset for admin-created staff — `StaffController`
  still sets the admin-typed password live, no temp-password/forced-reset
  flow.
- 403 page — no custom `resources/views/errors/403.blade.php` exists;
  still Laravel's default.
- Supervisor UI visual parity — confirmed now matches (uses
  `AdminPageBanner`, Lucide icons, same card/table styling as
  Admin/Trainee) — this one item from the README's older task list can
  be marked done, if it wasn't already.

---

## Updated Open Items Table

| Item | Status |
|---|---|
| Region-validation placeholder bug | **Resolved** (confirmed in StaffController + ProfileUpdateRequest) |
| Real email delivery | Brevo configured; school-domain deliverability still unresolved |
| Analytics route not wired to controller | **Fixed this pass** (one-line route change — see 9.1) |
| Analytics historical/trend tracking | Not built (current-moment stats only; this is now the accurate framing, not "static") |
| 2FA (Fortify) | **Built, previously undocumented** — see 9.3 |
| QR code on certificates (US4.1) | **Built** — see 9.4 |
| QR-scanning verification (US4.2) | Partially covered via native camera apps; no in-app scanner — see 9.5 |
| AI Learning Assistant (Epic 3) | Not built |
| Module-level filtering, Supervisor dashboard (US4.3) | Not built |
| Server-side exam timer validation | Not built |
| Forced password reset for admin-created staff | Not built |
| 403 page styling | Not built (Laravel default) |
| Supervisor UI visual parity | Done |
| "PNP's own localized infrastructure" (US4.4) | Architecture-level, not a code task — now sharper given 9.4's external QR API call |