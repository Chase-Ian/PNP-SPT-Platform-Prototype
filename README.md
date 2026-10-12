# SPT-Platform — PNP Learning Management System

A training/e-learning platform for PNP (Philippine National Police) officers,
built with Laravel 11 + Inertia.js + Vue 3, targeting eventual handover to
PNP ITMS.

**Local dev stack:** Laravel 11.56.1 · PHP 8.2.33 · MariaDB (XAMPP) ·
Vue 3 (Composition API) · Inertia.js v2 · Tailwind CSS · Laravel Breeze

**Production stack:** Render (Docker, multi-stage build) · TiDB Cloud
Serverless (MySQL-compatible) · AWS S3 (certificates, module files) ·
HTTPS enforced

---

## 🌐 Live deployment status: DEPLOYED

The app is deployed and running on Render, backed by TiDB Cloud and AWS S3.
Local data (courses, lessons, exam questions, demo accounts) was migrated
across via `mysqldump` → cleaned of MariaDB-specific `CHECK (json_valid())`
constraints → imported through TiDB Cloud's SQL Editor.

---

## Roles

Three roles, enforced via route-level `role:` middleware (not just hidden
nav links):

- **Trainee** — self-registers, takes courses/lessons/exams, earns certificates
- **Supervisor** — trainee-like base experience + a Monitoring tab for
  tracking assigned trainees' progress
- **Admin** — full platform management (courses, staff, exam content,
  certificates, analytics)

Supervisor and Admin accounts are **not self-registered** — only an Admin
can create them, via Manage Staff.

---

## Demo accounts

**Local development only.** In production, the `/dev/quick-login/{type}`
shortcut route has been **fully removed from the codebase** (not just
environment-gated) as a security hardening step before going live — see
Phase 8 below. On the live deployment, log in manually with these
credentials through the normal login form:

| Role | Email | Password |
|---|---|---|
| Trainee | maria.cruz@pnp.gov.ph | demo1234 |
| Supervisor | supervisor.demo@pnp.gov.ph | demo1234 |
| Admin | admin.demo@pnp.gov.ph | demo1234 |

---

## Phase status

### ✅ Phase 0 — Version & Security Decisions
Laravel 11.56.1 (pinned to `^11.44` for the file-validation CVE fix), PHP
8.2.33 confirmed compatible. Two remaining Laravel 11.x advisories
(signed-URL path confusion, CRLF injection) have no framework patch —
mitigated manually (see Security Mitigations below).

### ✅ Phase 1 — Scaffolding
Laravel + Breeze + Inertia + Vue installed. Local dev on XAMPP/MariaDB.

### ✅ Phase 2 — Database Schema
Full schema built incrementally: `users` (first_name/last_name/rank split,
region via PRO dropdown, `is_locked`/`locked_at`), `courses`, `enrollments`,
`modules`, `lessons` (JSON content blocks), `lesson_quiz_questions`,
`lesson_progress`, `module_completions`, `certificates`, `exam_questions`
(4 types via flexible `answer_data` JSON), `exam_settings`, `exam_attempts`,
notifications (Laravel built-in).

### ✅ Phase 3 — Auth & Role System
3-role auth, role-based route middleware (403 on cross-role access,
verified by manual URL testing), account locking with immediate session
kill via a shared `AccountLockService`. Quick Login dev shortcut used
throughout development, **removed entirely before production deployment**
(Phase 8).

### ✅ Phase 4 — Trainee Pages
Dashboard (real stats, gradient banner, collapsible sidebar), Course
Catalog (Catalog + My Learning tabs, live lesson-progress bars, Final Exam
gating), Certificates (View inline + Download), public Certificate
Verification, Notification bell. Full visual redesign applied — sidebar
nav, colored stat icons, real Lucide icons (replacing emoji) throughout.

### ✅ Phase 5 — Admin Pages
Dashboard (region-filterable Officer Progress table), Manage Courses
(course CRUD → drills into Manage Modules), Manage Staff (create/lock
Supervisor & Admin accounts), Monitor Officers (search + real Officer
Detail page with enrollments/certs/exam history + lock toggle),
Certificates Log (searchable, grouped by station), Analytics (static —
flagged for future real historical tracking). Same icon/banner redesign
applied as Phase 4.

### ✅ Phase 6 — Content Authoring System
The largest single feature area:
- **Lesson block editor** — structured content blocks only (heading,
  paragraph, bullet list, uploaded video, YouTube embed) — deliberately
  no raw HTML/`v-html` anywhere, closing an XSS risk given multiple
  admin/supervisor accounts across regions. Video-type blocks (upload or
  YouTube) limited to one per lesson, first or last position only.
- **PPTX text extraction** — pure-PHP (`phpoffice/phppresentation`),
  pre-fills the block editor from uploaded slides. (A richer slide-image
  conversion pipeline via LibreOffice + Ghostscript was attempted and
  shelved due to Windows/Apache profile-permission issues — text
  extraction only was kept.)
- **Full CRUD + preview** for lessons and lesson quiz questions
  (create/edit/delete + non-interactive trainee-view preview).
- **Sequential trainee lesson viewer** — locked/unlocked progression,
  per-lesson quiz gates the next lesson, module progress sidebar.
- **Final exam system** — 4 question types (Multiple Choice, True/False,
  Matching Type, Identification) via one flexible schema. Matching-type
  scoring is **all-or-nothing per question** (a partial-credit variant was
  tried and reverted). Merged with exam settings (time limit, live
  pass-threshold calculation using the real question count).
- **Exam-taking UX**: live countdown timer with auto-submit, pre-submit
  confirmation modal (flags unanswered questions), post-submit
  per-question correct/incorrect breakdown (never reveals the correct
  answer) — shown regardless of pass/fail. Same breakdown pattern applied
  to lesson quizzes, with an explicit "review the material again" redirect
  on a failed lesson quiz.
- **Account locking** — immediate session kill on lock, login-time gate.
- **Icon system pass** — `lucide-vue-next` installed; emoji icons replaced
  across `AdminLayout`, `AdminPageBanner`, `TraineeLayout`, Dashboard,
  Courses, Certificates, Verification, and Monitor Officers.

### ✅ Phase 7 — Production Infrastructure Setup
- **AWS S3**: dedicated IAM user (`spt-platform-app`) with a custom scoped
  policy (`SPTPlatformS3Access` — `ListBucket` on the bucket,
  `GetObject`/`PutObject`/`DeleteObject` on its contents only, no broader
  S3 access). Bucket created with Block Public Access fully enabled — the
  app is the sole gatekeeper for file access, consistent with the
  certificate-download expiry mitigation from Phase 6/0.
- **TiDB Cloud Serverless**: chosen as the production MySQL-compatible
  database. Local MariaDB data exported via `mysqldump`, cleaned of
  MariaDB-only `CHECK (json_valid(...))` constraints (TiDB/MySQL don't
  need or fully support this MariaDB-specific simulated-JSON-column
  syntax), and imported through TiDB Cloud's browser-based SQL Editor
  (bypassing local `mysql` client version limitations — the
  XAMPP-bundled MariaDB 10.4 client didn't support modern `--ssl-mode`
  flags needed for TiDB's enforced TLS connections).
- **Docker multi-stage build** (Render): three stages —
  `composer-build` (installs PHP deps, including compiling `gd` for
  `phpoffice/phpspreadsheet`) → `node-build` (npm + Vite build; crucially
  copies `vendor/` in from the composer stage first, since
  `resources/js/app.js` imports Ziggy's generated routes file from
  `vendor/tightenco/ziggy` — this dependency was missed on the first build
  attempt and caused a Rollup resolution failure) → final PHP-FPM +
  Nginx + Supervisord runtime image.
- **TiDB SSL/TLS**: Let's Encrypt ISRG Root X1 CA certificate baked into
  the final Docker image; `config/database.php` updated to pass
  `PDO::MYSQL_ATTR_SSL_CA` / `MYSQL_ATTR_SSL_VERIFY_SERVER_CERT` from env
  vars, resolving TiDB's "insecure transport" rejection.

### ✅ Phase 8 — Production Hardening (pre-launch)
- **`/dev/quick-login/{type}` removed entirely** from `routes/web.php` —
  not just environment-gated as originally built, but deleted outright,
  closing any possibility of an unauthenticated bypass in production.
- **`AuthenticatedSessionController`** updated to redirect by role
  (`admin.dashboard` / `supervisor.dashboard` / `dashboard`) after a normal
  login, replacing the hardcoded generic redirect that Quick Login used
  to handle.
- **HTTPS enforcement** — `URL::forceScheme('https')` added in
  `AppServiceProvider`, ensuring generated asset URLs and redirects use
  `https://` under Render's domain (avoids mixed-content issues behind
  Render's TLS-terminating proxy).
- **Environment lockdown** — `APP_ENV=production`, `APP_DEBUG=false`,
  `LOG_CHANNEL=stderr` (Render's log viewer reads stdout/stderr, not a log
  file on an ephemeral filesystem), real `APP_KEY` and `MYSQL_ATTR_SSL_CA`
  configured via Render's environment settings.

---

## Security mitigations (cumulative)

- Certificate downloads use a manual expiry check, never Laravel's
  built-in signed URLs (GHSA-crmm-hgp2-wgrp — unpatched in 11.x)
- Uploaded file MIME types validated server-side via `getMimeType()` +
  a ZIP-marker fallback check for PPTX files that `finfo` sometimes
  misdetects as generic binary data
- Files stored with an explicitly chosen extension, never Laravel's
  auto-detected one
- Email inputs sanitized against CRLF injection (GHSA-5vg9-5847-vvmq —
  unpatched in 11.x) on registration, profile updates, and staff account
  creation
- Lesson content stored as structured JSON blocks, rendered with plain
  `{{ }}` interpolation only — no `v-html` anywhere
- Route-level `role:` middleware on all admin/supervisor routes
- **Dev-only auth bypass (Quick Login) fully removed for production**,
  not just environment-gated
- **AWS S3 bucket kept fully private** (Block Public Access on) — the app
  is the only path to any stored file, preserving the expiry-check
  mitigation above
- **HTTPS enforced** at the application layer via `URL::forceScheme()`
- **TLS enforced on the database connection** to TiDB Cloud (CA-verified)

---

## Known recurring bug patterns (worth remembering)

- **Stale cached values drifting from the live source of truth** —
  `exam_settings.question_count` vs. the real question bank size surfaced
  as a bug in at least two separate places before being fixed everywhere.
  Prefer computing from the live relationship over trusting a cached
  column unless something specifically keeps it in sync.
- **Missing bidirectional model relationships** — e.g. needing
  `Enrollment::course()` alongside `Course::enrollments()`.
- **Forgetting to add new columns to `$fillable`** after a migration —
  silently no-ops the update instead of erroring, which made a few bugs
  (like account locking) look like they weren't running at all when they
  actually were, just not persisting.
- **Multi-stage Docker builds need their cross-stage dependencies made
  explicit** — a build stage only has what's explicitly `COPY --from=`'d
  into it; assuming another stage's output (like Composer's `vendor/`)
  is implicitly available is the exact bug that broke the first Render
  deploy attempt.

---

## Nice-to-have / deferred list

- Update Supervisor UI to match the Trainee/Admin visual redesign
- 403 page styling (currently Laravel's plain default page)
- Forced password reset / emailed temp password for admin-created staff
  accounts (currently the admin-typed password becomes the real password
  immediately, no reset flow)
- Server-side exam timer validation (currently client-side only)
- Real historical tracking for Analytics deltas (currently static — no
  snapshot table exists yet to compute real trends)
- Richer PPTX-to-visual-slide conversion — attempted, shelved in favor of
  text extraction only


# SPT-Platform — Security Advisory Reference

This document lists every advisory Composer flagged when installing
`laravel/framework` for this project, identified by their Packagist (`PKSA-`)
and/or GitHub (`GHSA-`) IDs, with a plain-language summary of each
vulnerability and its current status against our pinned version
(`laravel/framework ^11.44`).

See `docs/SECURITY_NOTES.md` for the code-level mitigations implemented for
the unpatched items.

---

## Advisories with a known summary

### GHSA-crmm-hgp2-wgrp — Temporary Signed URL Path Confusion
**Severity:** Moderate
**Status in 11.x:** ❌ Unpatched (fixed in Laravel 12.61.1 / 13.12.0)

Laravel's local filesystem driver can parse a temporary signed URL
ambiguously. A URL that should have expired may still be accepted, and in
some cases a request can resolve to a different file than the one that was
originally signed. An upload variant of the same issue could let a write
land somewhere other than the intended destination.

**Why it matters here:** our certificate download and verification flow is
exactly this kind of feature — a link that's supposed to expire and point to
one specific file.

**Mitigation:** we do not use `Storage::temporaryUrl()` or
`URL::temporarySignedRoute()`. Certificate downloads go through a controller
that checks expiry manually against `issued_at`. See `CertificateController`.

---

### GHSA-5vg9-5847-vvmq — CRLF Injection in Default Email Validation Rule
**Severity:** High
**Status in 11.x:** ❌ Unpatched (fixed in Laravel 12.60.0 / 13.10.0)

Laravel's built-in `email` validation rule, combined with how Symfony
Mailer/Mime handle certain character sequences, can allow an attacker to
inject line breaks into a user-supplied email address. This can let outbound
mail get manipulated — extra headers, unintended recipients, etc. — in any
flow that sends mail to a user-supplied address (registration, contact
forms, password resets).

**Why it matters here:** officer self-registration collects an email address
directly from the user.

**Mitigation:** email input is stripped of `\r`, `\n`, and their URL-encoded
forms before validation runs, in every form that accepts a user-supplied
email. See `prepareForValidation()` in the relevant Form Request classes.

---

### GHSA-78fx-h6xr-vch4 — Wildcard File Validation Bypass
**Severity:** Moderate (CVE-2025-27515)
**Status in 11.x:** ✅ Patched in 11.44.1

When validating a file/image field using wildcard rules (`files.*`), a
crafted request could bypass the validation entirely — meaning a file that
should have been rejected (wrong type, wrong size) could get through.

**Why it matters here:** admin module uploads accept PDF/PPTX/MP4 files.

**Mitigation:** even though our pinned version (`^11.44`) includes the fix,
we additionally verify each uploaded file's real MIME type server-side
(via `finfo`, not the client-supplied extension) as defense-in-depth. See
`ModuleController::store()`.

---

### SBA-ADV-20241209-01 — Debug-Mode Reflected XSS (query/body)
**Severity:** Low in production (requires `APP_DEBUG=true`)
**Status:** Not a framework code fix — a configuration issue

When `APP_DEBUG=true` and the app returns a 5xx error, Laravel's error page
embeds request data (URL query parameters and request body values) without
escaping it. A crafted link could execute JavaScript in a visitor's browser
if they hit a debug-mode error page.

**Mitigation:** `APP_DEBUG=false` is required in every non-local environment.
Confirm this in `.env` before any deployment, including Railway staging.

---

### SBA-ADV-20241209-02 — Debug-Mode Reflected XSS (URL path)
**Severity:** Low in production (requires `APP_DEBUG=true`)
**Status:** Same issue as above, different injection point (URL path
segments instead of query/body)

**Mitigation:** same as above — `APP_DEBUG=false` outside local development.

---

## Confirmed PKSA → GHSA mapping (via `composer audit`, run against `laravel/framework ^11.44`)

`composer audit` resolved the following mapping directly — no longer
inferred. All three are advisories we already had summaries and mitigations
for; `composer audit` just confirms which Packagist ID corresponds to which
GitHub advisory, and shows one bug (CRLF injection) is catalogued under two
separate PKSA entries (one gained a CVE number after initial publication).

| PKSA ID | Maps to | Notes |
|---|---|---|
| `PKSA-m5cs-t1y6-qpcs` | GHSA-crmm-hgp2-wgrp — Signed URL Path Confusion | Severity: medium, no CVE assigned. Same bug described above; mitigated in `CertificateController`. |
| `PKSA-3r5d-mb8f-1qw9` | GHSA-5vg9-5847-vvmq — CRLF injection in default email rule | Severity: high, no CVE at time of this listing. Mitigated via `prepareForValidation()`. |
| `PKSA-mdq4-51ck-6kdq` | GHSA-5vg9-5847-vvmq (same bug) — later assigned **CVE-2026-48019** | Duplicate Packagist entry for the same CRLF issue, now with a CVE attached. Same mitigation applies. |

These three IDs are ignored in `composer.json` →
`config.policy.advisories.ignore-id`, each tied to a documented, code-level
mitigation above — not a blanket suppression.

## Advisory still unconfirmed

| ID | Status |
|---|---|
| `PKSA-8qx3-n5y5-vvnd` | Not yet resolved by `composer audit` output seen so far — run `composer audit` again and check if this one still appears; if so, look it up at `https://packagist.org/security-advisories/PKSA-8qx3-n5y5-vvnd` and add its summary here before handover |

Two IDs from the original create-project error (`PKSA-q46n-4fdk-zjr4`,
`PKSA-qzrn-rnz3-85w1`) no longer appear once the version was narrowed to
`^11.44` — they most likely applied only to framework versions below 11.44
and were resolved simply by landing on a newer patch. No action needed
unless they resurface.

---


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