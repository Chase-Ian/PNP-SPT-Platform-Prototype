# Certificate fonts

`URWGothic-Book`, `-BookOblique`, `-Demi` and `-DemiOblique` set the paragraph
on the certificate PDF (`resources/views/certificates/template.blade.php`).
The official certificate uses Century Gothic there, but that is a Microsoft
Office font whose license doesn't allow shipping it with the app. URW Gothic
is a free twin of it, with the same letter shapes and widths.

- Source: https://github.com/ArtifexSoftware/urw-base35-fonts (`fonts/`)
- Copyright (URW)++ Design & Development
- License: GNU AGPL v3, with an exception that lets PDF and PostScript
  documents embed the fonts whatever license the document itself has. The
  certificate PDFs embed a subset, so they are not affected. `LICENSE` (the
  exception) and `COPYING` (the AGPL v3 text) next to this file are copied
  from that repository.

dompdf copies these files into `storage/fonts/` (its font cache) the first
time a certificate is rendered.
