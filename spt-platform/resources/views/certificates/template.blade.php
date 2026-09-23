{{-- resources/views/certificates/template.blade.php --}}
{{-- 1-Page Official Certificate of Participation --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Certificate of Participation — {{ $name }}</title>
    <style>
        @page {
            size: 297mm 210mm landscape;
            margin: 0;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        @font-face {
            font-family: 'DejaVu Sans';
            src: url('{{ public_path('vendor/dompdf/dompdf/lib/fonts/DejaVuSans.ttf') }}') format('truetype');
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            width: 297mm;
            height: 210mm;
            background: #ffffff;
            color: #222222;
        }

        .cert-container {
            position: absolute;
            top: 8mm;
            left: 8mm;
            right: 8mm;
            bottom: 8mm;
            border: 3px solid #B8860B;
            padding: 10mm 14mm 8mm 14mm;
            background: #ffffff;
        }

        /* Inner subtle gold border */
        .inner-border {
            position: absolute;
            top: 3mm;
            left: 3mm;
            right: 3mm;
            bottom: 3mm;
            border: 1px solid rgba(184, 134, 11, 0.35);
            pointer-events: none;
        }

        /* Decorative corner ornaments */
        .corner-tl, .corner-tr, .corner-bl, .corner-br {
            position: absolute;
            width: 9mm;
            height: 9mm;
            border-color: #DAA520;
            border-style: solid;
        }
        .corner-tl { top: 4.5mm; left: 4.5mm; border-width: 2px 0 0 2px; }
        .corner-tr { top: 4.5mm; right: 4.5mm; border-width: 2px 2px 0 0; }
        .corner-bl { bottom: 4.5mm; left: 4.5mm; border-width: 0 0 2px 2px; }
        .corner-br { bottom: 4.5mm; right: 4.5mm; border-width: 0 2px 2px 0; }

        /* Tables for layout (DomPDF reliable) */
        table.layout-table {
            width: 100%;
            border-collapse: collapse;
        }

        /* ===== HEADER ROW ===== */
        .logo-box {
            display: inline-block;
            vertical-align: middle;
            text-align: center;
        }

        .logo-circle {
            width: 17mm;
            height: 17mm;
            border-radius: 50%;
            border: 2px solid #0a1a4e;
            background: #f0f4ff;
            text-align: center;
            line-height: 1.2;
            padding-top: 3.5mm;
            display: inline-block;
        }

        .logo-circle span {
            font-size: 5pt;
            font-weight: bold;
            color: #0a1a4e;
            text-align: center;
            display: block;
        }

        .logo-divider {
            display: inline-block;
            width: 1px;
            height: 12mm;
            background: #cccccc;
            margin: 0 4mm;
            vertical-align: middle;
        }

        .cert-header {
            text-align: right;
        }

        .cert-this {
            font-size: 9pt;
            color: #666666;
            letter-spacing: 3px;
            text-transform: uppercase;
            margin-bottom: 1mm;
        }

        .cert-title {
            font-size: 26pt;
            font-weight: bold;
            color: #0a1a4e;
            line-height: 1.05;
            letter-spacing: 0.5px;
        }

        .cert-subtitle {
            font-size: 26pt;
            font-weight: bold;
            color: #0a1a4e;
            line-height: 1.05;
            letter-spacing: 0.5px;
        }

        /* ===== ISSUED TO ===== */
        .issued-section {
            text-align: center;
            margin-top: 5mm;
            margin-bottom: 3mm;
        }

        .issued-to-label {
            font-size: 8.5pt;
            color: #555555;
            letter-spacing: 3px;
            text-transform: uppercase;
            margin-bottom: 2mm;
        }

        .trainee-name-wrap {
            text-align: center;
        }

        .trainee-name {
            font-size: 22pt;
            font-weight: bold;
            color: #0a1a4e;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            border-bottom: 2px solid #0a1a4e;
            padding-bottom: 1.5mm;
            display: inline-block;
            min-width: 120mm;
        }

        /* ===== DESCRIPTION ===== */
        .description-wrap {
            text-align: center;
            margin: 4mm auto;
            max-width: 240mm;
        }

        .description {
            font-size: 9pt;
            color: #222222;
            line-height: 1.65;
            text-align: center;
        }

        /* ===== BOTTOM SECTION ===== */
        .bottom-table {
            width: 100%;
            margin-top: 4mm;
            border-collapse: collapse;
        }

        .qr-img {
            width: 23mm;
            height: 23mm;
            border: 1px solid #cccccc;
            padding: 1mm;
            background: #ffffff;
        }

        .verify-info {
            font-size: 5.5pt;
            color: #333333;
            line-height: 1.6;
            vertical-align: middle;
            padding-left: 3mm;
        }

        .verify-info .label {
            font-weight: bold;
            color: #0a1a4e;
        }

        .verify-note {
            color: #777777;
            font-style: italic;
            margin-top: 1mm;
            font-size: 5pt;
        }

        /* Signature block */
        .signature-cell {
            text-align: center;
            vertical-align: bottom;
            width: 75mm;
        }

        .sig-line {
            width: 60mm;
            margin: 0 auto;
            border-bottom: 1.5px solid #333333;
            padding-bottom: 1mm;
            height: 10mm;
        }

        .sig-placeholder {
            color: #999999;
            font-size: 5.5pt;
            font-style: italic;
            line-height: 10mm;
        }

        .sig-name {
            font-weight: bold;
            font-size: 8pt;
            color: #0a1a4e;
            margin-top: 1.5mm;
            letter-spacing: 0.5px;
        }

        .sig-title {
            font-size: 6.5pt;
            color: #333333;
            margin-top: 0.5mm;
        }

        .sig-office {
            font-size: 6.5pt;
            color: #555555;
            margin-top: 0.5mm;
        }
    </style>
</head>
<body>

<div class="cert-container">

    {{-- Decorative inner border & corners --}}
    <div class="inner-border"></div>
    <div class="corner-tl"></div>
    <div class="corner-tr"></div>
    <div class="corner-bl"></div>
    <div class="corner-br"></div>

    {{-- Top Header Table: Logos (Left) & Title (Right) --}}
    <table class="layout-table" style="margin-bottom: 4mm;">
        <tr>
            <td style="vertical-align: middle; width: 45%;">
                <div class="logo-box">
                    <div class="logo-circle">
                        <span>PNP<br>SPT</span>
                    </div>
                </div>
                <div class="logo-divider"></div>
                <div class="logo-box">
                    <div class="logo-circle">
                        <span>BAGONG<br>PIL.</span>
                    </div>
                </div>
            </td>
            <td style="vertical-align: middle; width: 55%; text-align: right;">
                <div class="cert-header">
                    <div class="cert-this">This</div>
                    <div class="cert-title">CERTIFICATE</div>
                    <div class="cert-subtitle">OF PARTICIPATION</div>
                </div>
            </td>
        </tr>
    </table>

    {{-- Issued To Section --}}
    <div class="issued-section">
        <div class="issued-to-label">is issued to</div>
        <div class="trainee-name-wrap">
            <span class="trainee-name">{{ strtoupper($name) }}</span>
        </div>
    </div>

    {{-- Training Description --}}
    <div class="description-wrap">
        <div class="description">
            for participating in the {{ $duration_hours }}-hour training on <strong>{{ $course }}</strong>
            conducted by the Philippine National Police Special Police Training Division{{ $region ? ', ' . $region : '' }}
            on <strong>{{ $date }}</strong>.
        </div>
    </div>

    {{-- Bottom Verification & Signature Section --}}
    <table class="bottom-table">
        <tr>
            {{-- Left: QR Code + Verification Metadata --}}
            <td style="vertical-align: bottom; width: 60%;">
                <table style="border-collapse: collapse;">
                    <tr>
                        <td style="vertical-align: middle;">
                            <img class="qr-img" src="{{ $qr_code_data_uri }}" alt="QR Code">
                        </td>
                        <td class="verify-info">
                            <div><span class="label">Verify Link:</span> {{ $verify_url }}</div>
                            <div><span class="label">Certificate No:</span> {{ $serial_id }}</div>
                            <div><span class="label">Training Ctrl No:</span> {{ $training_ctrl_no }}</div>
                            <div><span class="label">Unit / Office:</span> {{ $unit_office }}</div>
                            <div class="verify-note">
                                This certificate can be verified by clicking the link<br>
                                or scanning the QR Code.
                            </div>
                        </td>
                    </tr>
                </table>
            </td>

            {{-- Right: Signature Block --}}
            <td class="signature-cell">
                <div class="sig-line">
                    <span class="sig-placeholder">— Signature —</span>
                </div>
                <div class="sig-name">OFFICER-IN-CHARGE</div>
                <div class="sig-title">Training Director</div>
                <div class="sig-office">PNP Special Police Training Division</div>
            </td>
        </tr>
    </table>

</div>

</body>
</html>