# PNP-SPT-Platform-Prototype

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

# SPT-Platform — Environment & Version Notes

Tracks known gaps between the local development environment and the target
deployment environment, so nothing here is a silent surprise during
handover or production deployment.

---

## Database engine: MariaDB (local) vs MySQL 9.1 (target)

**Local dev:** XAMPP bundles **MariaDB 10.4.32**, not MySQL. Laravel's
`mysql` database driver connects to both without code changes, so this does
not block development.

**Target:** PNP ITMS's data center runs **MySQL 9.1** per the original
architecture spec.

**Why this matters:** MariaDB and MySQL have diverged since MariaDB forked
from MySQL, and by MySQL 9.x the gap is larger than in earlier versions.
Specific things to verify before/at handover rather than assume:

- **JSON columns/functions** — used in `lesson_progress.state` and
  `exam_attempts.answers`. MySQL 9.x's JSON function set and validation
  behavior differs from MariaDB 10.4's older implementation.
- **Window functions** — if any reporting/analytics queries use them
  (e.g. for the Admin Analytics dashboard), confirm syntax compatibility.
- **Default collation/charset behavior** — can differ between the two
  engines and affect sorting or comparison of officer names, emails, etc.
- **`ENUM` and check-constraint behavior** — used in several tables
  (`role`, `status`, `file_type` columns).

**Action before handover:** either test the schema and key queries directly
against a real MySQL 9.1 instance (e.g. via the Docker Compose setup already
planned for this project), or explicitly flag to PNP ITMS that local
development was done against MariaDB and request a validation pass on
their actual MySQL 9.1 environment before go-live.

---

## Recommendation

Once the core application is functional against local MariaDB, switch local
development to the Docker Compose setup (already planned — `mysql:9.1`
image) so ongoing feature work is validated against the real target engine
rather than discovering engine-specific issues late.


## Before handover checklist

- [ ] Re-run `composer audit` and compare against this list
- [ ] Check whether Laravel has since released an 11.x patch for
      GHSA-crmm-hgp2-wgrp or GHSA-5vg9-5847-vvmq — if so, remove the
      corresponding manual mitigation and the ignore entry
- [ ] Confirm `APP_DEBUG=false` in every deployed environment
- [ ] Verify each `PKSA-` ID above still resolves to something irrelevant to
      this app before shipping to PNP ITMS