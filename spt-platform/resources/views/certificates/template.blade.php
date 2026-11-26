{{-- resources/views/certificates/template.blade.php --}}
{{--
    Certificate of Participation, laid out after the official DICTM
    certificate: A4 landscape, with positions and sizes measured from a scan.

    Its fonts follow that certificate too. The fixed wording that it sets in
    Office fonts is vector art in resources/certificate/ (build-lettering.php
    there explains why, and redraws it). Arial text uses the PDF's built-in
    Helvetica, which has the same letter shapes and widths, and the paragraph
    uses URW Gothic, a free twin of Century Gothic (resources/fonts/).
--}}
@php
    // Printed under the signature: put the Director's name and position here.
    $signatoryName = 'OFFICER-IN-CHARGE';
    $signatoryTitle = 'Director for ICT Management';

    $file = fn (string $path) => str_replace('\\', '/', resource_path($path));

    // Rank and name, e.g. "PLTCOL JUAN DELA CRUZ". The sample sets it nearly
    // edge to edge at 40pt with letters spaced 1pt apart, so a longer name
    // shrinks to stay on one line, and its baseline stays at 98mm.
    $holder = mb_strtoupper(trim($rank.' '.$name));
    $metrics = app('dompdf')->getFontMetrics();
    $holderWidth = $metrics->getTextWidth($holder, $metrics->getFont('Helvetica', 'bold'), 40, 0, 1);
    $holderSize = round(min(40, 40 * 760 / max($holderWidth, 1)), 1); // 760pt = 268mm
    $holderTop = round(98.0 - 0.814 * $holderSize * 25.4 / 72, 2); // see "Text placement" below

    // The citation has room for four lines (about 360 characters), as on the
    // sample. A course title long enough to need more gets smaller type.
    $citationSize = round(min(16.5, 16.5 * sqrt(360 / (mb_strlen($course) + 210))), 1);
    $citationLeading = round($citationSize * 15.7 / 16.5, 2);
@endphp
{{--
    Text placement: dompdf makes a line box line-height x (font height / size)
    tall, a ratio of 1.0175 for Helvetica and 1.265 for URW Gothic, and puts
    the baseline 0.8 of the way down it. The tops and line heights below are
    worked back from the baselines measured on the sample.
--}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Certificate of Participation: {{ $name }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 0;
        }

        @font-face {
            font-family: 'URW Gothic';
            font-style: normal;
            font-weight: normal;
            src: url('{{ $file('fonts/URWGothic-Book.ttf') }}') format('truetype');
        }
        @font-face {
            font-family: 'URW Gothic';
            font-style: italic;
            font-weight: normal;
            src: url('{{ $file('fonts/URWGothic-BookOblique.ttf') }}') format('truetype');
        }
        @font-face {
            font-family: 'URW Gothic';
            font-style: normal;
            font-weight: bold;
            src: url('{{ $file('fonts/URWGothic-Demi.ttf') }}') format('truetype');
        }
        @font-face {
            font-family: 'URW Gothic';
            font-style: italic;
            font-weight: bold;
            src: url('{{ $file('fonts/URWGothic-DemiOblique.ttf') }}') format('truetype');
        }

        * {
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Helvetica, Arial, sans-serif;
            color: #1f1f1f;
        }

        /* Every block is placed on the page itself, in mm from its top left. */
        .sheet, .frame, .watermark, .logo, .line, .verify {
            position: absolute;
        }

        .line {
            left: 0;
            width: 297mm;
            text-align: center;
        }

        .line img {
            display: block;
            margin: 0 auto;
        }

        .sheet {
            top: 0;
            left: 0;
            width: 297mm;
            height: 210mm;
            background: #ededeb;
        }

        .frame {
            top: 5mm;
            left: 5mm;
            width: 283mm;
            height: 196mm;
            border: 2mm solid #474852;
        }

        /* The faded DICTM seal behind the lower right, cut off by the frame. */
        .watermark {
            top: 7mm;
            left: 7mm;
            width: 283mm;
            height: 196mm;
            overflow: hidden;
        }

        .watermark img {
            position: absolute;
            top: 92mm;
            left: 197mm;
            width: 116mm;
            height: 116mm;
            opacity: 0.11;
        }

        /* ===== HEADER ===== */
        .logo-pnp {
            top: 27mm;
            left: 29.6mm;
            height: 27mm;
        }

        .logo-dictm {
            top: 27.5mm;
            left: 248mm;
            width: 28mm;
            height: 28mm;
        }

        .header {
            top: 27.25mm;
            font-size: 12.5pt;
            line-height: 13pt;
            color: #1a1a1a;
        }

        .header .camp {
            font-size: 11.5pt;
        }

        /* ===== TITLE AND NAME ===== */
        .title {
            top: 56.05mm;
        }

        .presented {
            top: 73.85mm;
        }

        .holder {
            font-weight: bold;
            line-height: 1;
            letter-spacing: 0.025em;
            color: #3a4687;
            white-space: nowrap;
        }

        /* The sample's name has a soft grey drop shadow. */
        .holder.shadow {
            margin-left: 0.45mm;
            margin-top: 0.45mm;
            color: #a4a5aa;
        }

        /* ===== CITATION ===== */
        .citation {
            top: 112.4mm;
            left: 12mm;
            width: 273mm;
            font-family: 'URW Gothic', sans-serif;
            font-size: 16.5pt;
            line-height: 15.7pt; /* 7mm apart, like the sample */
            color: #262626;
        }

        .given {
            top: 144.65mm;
            font-size: 16.5pt;
            line-height: 18.5pt;
        }

        .given sup {
            font-size: 62%;
            vertical-align: super;
            line-height: 0;
        }

        /* ===== SIGNATORY ===== */
        .sig-name {
            top: 176.15mm;
            font-size: 20.8pt;
            font-weight: bold;
            line-height: 1;
            color: #1c1c1c;
        }

        .sig-title {
            top: 183.8mm;
            font-size: 16.8pt;
            line-height: 1;
            color: #1c1c1c;
        }

        .tagline {
            top: 194.15mm;
        }

        /* ===== VERIFICATION (QR code and numbers, lower left) ===== */
        .verify {
            top: 174mm;
            left: 13mm;
            width: 80mm;
        }

        .verify table {
            border-collapse: collapse;
        }

        .verify td {
            vertical-align: middle;
        }

        .qr {
            width: 21mm;
            height: 21mm;
            background: #ffffff;
        }

        .verify-info {
            padding-left: 2.5mm;
            font-size: 6.5pt;
            line-height: 1.45;
            color: #333333;
        }

        .verify-info .label {
            font-weight: bold;
            color: #24397e;
        }

        .verify-url {
            word-wrap: break-word;
        }

        .verify-note {
            margin-top: 0.8mm;
            font-style: italic;
            color: #666666;
        }
    </style>
</head>
<body>

<div class="sheet"></div>
<div class="watermark"><img src="{{ $file('certificate/dictm-logo.png') }}" alt=""></div>
<div class="frame"></div>

{{-- Header: PNP and DICTM logos either side of the office's address block --}}
<img class="logo logo-pnp" src="{{ $file('certificate/pnp-logo.png') }}" alt="Philippine National Police">
<img class="logo logo-dictm" src="{{ $file('certificate/dictm-logo.png') }}" alt="DICTM">

<div class="line header">
    Republic of the Philippines<br>
    National Police Commission<br>
    <b>NATIONAL HEADQUARTERS, PHILIPPINE NATIONAL POLICE</b><br>
    <b>DIRECTORATE FOR INFORMATION AND COMMUNICATIONS TECHNOLOGY MANAGEMENT</b><br>
    <span class="camp">Camp BGen Rafael T Crame, Quezon City</span>
</div>

<div class="line title"><img src="{{ $file('certificate/title.svg') }}" alt="Certificate of Participation"></div>
<div class="line presented"><img src="{{ $file('certificate/presented-to.svg') }}" alt="is presented to"></div>

<div class="line holder shadow" style="top: {{ $holderTop }}mm; font-size: {{ $holderSize }}pt;">{{ $holder }}</div>
<div class="line holder" style="top: {{ $holderTop }}mm; font-size: {{ $holderSize }}pt;">{{ $holder }}</div>

<div class="line citation" style="font-size: {{ $citationSize }}pt; line-height: {{ $citationLeading }}pt;">
    In recognition of the active participation and successful completion of the
    {{ $duration_hours }}-hour online training on <b><i>&ldquo;{{ $course }}&rdquo;</i></b>
    conducted by the Directorate for Information and Communications Technology Management (DICTM).
</div>

<div class="line given">
    Given this {{ $issued_at->format('j') }}<sup>{{ $issued_at->format('S') }}</sup> day of
    {{ $issued_at->format('F Y') }} at Camp BGen Rafael T Crame, Quezon City.
</div>

{{-- Signatory: the space above the name is left for the signature --}}
<div class="line sig-name">{{ $signatoryName }}</div>
<div class="line sig-title">{{ $signatoryTitle }}</div>

<div class="line tagline"><img src="{{ $file('certificate/tagline.svg') }}" alt="DICTM: Leading digital transformation"></div>

{{-- Verification: QR code and numbers --}}
<div class="verify">
    <table>
        <tr>
            <td><img class="qr" src="{{ $qr_code_data_uri }}" alt="QR code"></td>
            <td class="verify-info">
                <div><span class="label">Certificate No.:</span> {{ $serial_id }}</div>
                <div><span class="label">Training Ctrl No.:</span> {{ $training_ctrl_no }}</div>
                <div><span class="label">Unit / Office:</span> {{ $unit_office }}</div>
                <div class="verify-url"><span class="label">Verify:</span> {{ $verify_url }}</div>
                <div class="verify-note">Scan the QR code or open the link to check this certificate.</div>
            </td>
        </tr>
    </table>
</div>

</body>
</html>
