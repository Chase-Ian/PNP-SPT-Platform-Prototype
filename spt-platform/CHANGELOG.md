# PNP-SPT Platform  -  Changelog

All notable changes to this project are documented here.
Format: `[Date]  -  Session Title` with categorized bullet points.

---

## [2026-09-23]  -  Single-Page Certificate Refactor & GD Extension Fix

### Fixed
- **Certificate Multi-Page Issue (4 Pages -> 1 Page)** (`resources/views/certificates/template.blade.php`)
  - Refactored layout to strictly fit on **1 single page** (A4 Landscape: 297mm x 210mm).
  - Removed unsupported CSS Flexbox layout (`display: flex`) and disconnected sidebar that previously caused DomPDF to split content across 4 pages.
  - Replaced structure with DomPDF-compliant HTML layout tables and calibrated padding/line-heights.
  - Eliminated footer overflow that previously pushed `"PNP Special Police Training Division"` onto an orphaned 4th page.
  - Preserved all certificate details on the single page:
    - Double gold border with decorative corner accents
    - Top header logo placeholders
    - Certificate title headers
    - Dynamic Trainee Name with underline
    - Dynamic training description paragraph with hours, course title, office/region, and date
    - Bottom-left QR code image and complete verification metadata (Verify Link, Certificate No., Training Ctrl No., Unit/Office, verification guide)
    - Bottom-right signature block placeholder (` -  Signature  - `, `OFFICER-IN-CHARGE`, `Training Director`, `PNP Special Police Training Division`)
  - Regenerated all existing stored certificate PDFs in S3/storage to the clean 1-page format.

- **Exam Submission 500 Error (PHP GD Extension)**
  - Fixed 500 Server Error when submitting the final exam: DomPDF threw `The PHP GD extension is required, but is not installed.` when attempting to embed images into the auto-generated certificate.
  - Enabled `extension=gd` in XAMPP configuration (`C:\xampp\php\php.ini`).
  - Verified `gd` is active via `php -m` and restarted the Laravel development server.

### Environment & Database
- **Live Team Environment Setup (`env.local` -> `.env`)**
  - Configured project to connect to TiDB Cloud (MySQL) and AWS S3 storage.
  - Fixed Windows SSL CA bundle paths for TiDB Cloud and AWS S3 (`MYSQL_ATTR_SSL_CA` & `AWS_CA_BUNDLE` set to `C:/xampp/perl/vendor/lib/Mozilla/CA/cacert.pem`).
  - Successfully ran database migrations on the live TiDB database (`training_ctrl_no` added to `certificates` table).
  - Verified live database connection and S3 file storage upload operations.

---

## [2026-09-23]  -  Certificate of Participation Feature

### Added
- **Certificate of Participation Template** (`resources/views/certificates/template.blade.php`)
  - Full A4 landscape design with dark navy + gold color scheme
  - Left panel: PNP-SPT seal emblem, gold ribbon, gold swoosh decoration
  - Right panel: PNP-SPT logo placeholders, certificate title, trainee name, description paragraph
  - Bottom-left: QR code + Verify Link + Certificate No. + Training Ctrl No. + Unit/Office
  - Bottom-right: Signature placeholder block (Officer-in-Charge / Training Director)
  - All fields auto-filled from database  -  no manual input required

- **Training Control Number** (`training_ctrl_no`)
  - New field added to `certificates` table via migration
  - Format: `SPT-[REGION]-[YEAR]-[SEQUENCE]` (e.g., `SPT-CALB-2026-0001` or `SPT-PRON-2026-0002`)
  - Auto-generated when trainee passes the final exam

- **Certificate No. format updated**
  - Old format: `PNP-2026-000001`
  - New format: `PNP-SPT-CP-2026-000001`

- **QR Code on Certificate**
  - Generated via `api.qrserver.com`
  - QR links to the public verification page: `/verify/{serial_id}`
  - Embedded as base64 data URI inside the PDF (with offline SVG fallback)

- **PNP-SPT Seal Image** (`public/pnp_seal.jpg`)
  - AI-generated placeholder seal (navy blue + gold, Philippine sun, shield, laurel leaves)
  - To be replaced with official PNP logo when assets are available

- **DomPDF Configuration** (`config/dompdf.php`)
  - Published and configured with `is_remote_enabled = true` to allow QR image fetching
  - Default paper: A4 landscape

### Changed
- **`Certificate` model** (`app/Models/Certificate.php`)
  - Added `training_ctrl_no` to `$fillable`
  - Added `getVerificationUrlAttribute()` accessor  -  returns `url('/verify/{serial_id}')`

- **`CertificateController`** (`app/Http/Controllers/CertificateController.php`)
  - `index()`  -  now passes `training_ctrl_no`, `verification_url`, formatted date to Vue
  - `view()`  -  now regenerates PDF on-the-fly from the template
  - `download()`  -  uses template for every download
  - `verify()`  -  now passes `unit_office`, `region`, `training_ctrl_no`, `verification_url`
  - Added `buildCertificatePdf()` helper method (shared by view + download)
  - Added `generateQrDataUri()` helper method (fetches QR from API, SVG fallback)

- **`ExamController`** (`app/Http/Controllers/ExamController.php`)
  - `issueCertificate()`  -  generates `training_ctrl_no`, fetches QR code, passes all new fields to template
  - PDF is now rendered as A4 landscape

- **`Certificates/Index.vue`** (`resources/js/Pages/Certificates/Index.vue`)
  - Premium redesign: gradient navy header banner, card layout with gold accent top bar
  - Now shows Certificate No. and Training Ctrl No. per certificate card
  - Added "Copy" button for the Verify Link (clipboard API)
  - Added "Verify" quick-link button (opens `/verify/{serial}`)

- **`Verify/Index.vue`** (`resources/js/Pages/Verify/Index.vue`)
  - Full redesign: gradient header, step-by-step verification guide
  - Input placeholder updated to new serial format (`PNP-SPT-CP-2026-000001`)

- **`Verify/Show.vue`** (`resources/js/Pages/Verify/Show.vue`)
  - Full redesign: green success banner, detailed info grid
  - Now shows: Holder Name, Course, Date, Unit/Office, Region, Cert No., Training Ctrl No., Verify URL
  - Added QR code hint note at bottom

### Database
- **Migration added:** `2026_09_23_000001_add_training_ctrl_no_to_certificates_table.php`
  - Adds `training_ctrl_no` (nullable string) to `certificates` table after `serial_id`
  - Status: MIGRATED on both local and live TiDB Cloud databases

### Dependencies Installed
- `endroid/qr-code` ^6.0 (via Composer)
- `bacon/bacon-qr-code` v3.1.1 (pulled in as dependency)

---

## Pending / Future Tasks

- [ ] Replace PNP seal  -  swap `public/pnp_seal.jpg` with the official PNP or PNP-SPT logo file when provided by the agency
- [ ] Add real agency logos to certificate header (PNP logo + Bagong Pilipinas logo) once asset files are available
- [ ] Signature block  -  once the official signatory is identified, update the signatory's full name, exact rank/title, and office in the certificate template (or upload an official signature graphic)
- [ ] Description wording  -  update the certificate description paragraph text if customized wording is requested for specific training categories
