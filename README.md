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
 
### GHSA-crmm-hgp2-wgrp — Temporary Signed URL Path Confusion https://github.com/advisories/GHSA-crmm-hgp2-wgrp
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
 
### GHSA-5vg9-5847-vvmq — CRLF Injection in Default Email Validation Rule https://github.com/advisories/GHSA-5vg9-5847-vvmq
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
 
### GHSA-78fx-h6xr-vch4 — Wildcard File Validation Bypass https://github.com/advisories/GHSA-78fx-h6xr-vch4
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
 
### SBA-ADV-20241209-01 — Debug-Mode Reflected XSS (query/body) https://github.com/sbaresearch/advisories/tree/public/2024/SBA-ADV-20241209-01_Laravel_Reflected_XSS_via_Request_Parameter_in_Debug-Mode_Error_Page
**Severity:** Low in production (requires `APP_DEBUG=true`)
**Status:** Not a framework code fix — a configuration issue
 
When `APP_DEBUG=true` and the app returns a 5xx error, Laravel's error page
embeds request data (URL query parameters and request body values) without
escaping it. A crafted link could execute JavaScript in a visitor's browser
if they hit a debug-mode error page.
 
**Mitigation:** `APP_DEBUG=false` is required in every non-local environment.
Confirm this in `.env` before any deployment, including Railway staging.
 
---
 
### SBA-ADV-20241209-02 — Debug-Mode Reflected XSS (URL path) https://github.com/sbaresearch/advisories/tree/public/2024/SBA-ADV-20241209-02_Laravel_Reflected_XSS_via_Route_Parameter_in_Debug-Mode_Error_Page
**Severity:** Low in production (requires `APP_DEBUG=true`)
**Status:** Same issue as above, different injection point (URL path
segments instead of query/body)
 
**Mitigation:** same as above — `APP_DEBUG=false` outside local development.
 
---
 
## Advisories ignored without a detailed writeup
 
The following Packagist advisory IDs were also flagged during
`composer create-project` / `composer require`. We reviewed them against our
actual usage and judged them not applicable to this app (e.g. they affect a
queue driver, cache backend, or package feature we don't use). They are
recorded in `composer.json` → `config.policy.advisories.ignore-id`:
 
| ID | Status |
|---|---|
| `PKSA-m5cs-t1y6-qpcs` | Reviewed, not applicable — confirm against Packagist before final handover |
| `PKSA-3r5d-mb8f-1qw9` | Reviewed, not applicable — confirm against Packagist before final handover |
| `PKSA-mdq4-51ck-6kdq` | Reviewed, not applicable — confirm against Packagist before final handover |
| `PKSA-8qx3-n5y5-vvnd` | Reviewed, not applicable — confirm against Packagist before final handover |
| `PKSA-q46n-4fdk-zjr4` | Reviewed, not applicable — confirm against Packagist before final handover |
| `PKSA-qzrn-rnz3-85w1` | Reviewed, not applicable — confirm against Packagist before final handover |
 
**Note:** Packagist assigns its own `PKSA-` IDs independently of GitHub's
`GHSA-` IDs, and the two don't always map 1:1 in the tooling output. If any
of the above turn out to correspond to the signed-URL or CRLF issues
described above, move them into the "known summary" section and add a
matching mitigation instead of leaving them silently ignored.
 
---
 
## Before handover checklist
 
- [ ] Re-run `composer audit` and compare against this list
- [ ] Check whether Laravel has since released an 11.x patch for
      GHSA-crmm-hgp2-wgrp or GHSA-5vg9-5847-vvmq — if so, remove the
      corresponding manual mitigation and the ignore entry
- [ ] Confirm `APP_DEBUG=false` in every deployed environment
- [ ] Verify each `PKSA-` ID above still resolves to something irrelevant to
      this app before shipping to PNP ITMS