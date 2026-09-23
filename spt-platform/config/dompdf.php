<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Settings
    |--------------------------------------------------------------------------
    |
    | Set some default values. It is possible to add all defines that can be set
    | in dompdf (see dompdf documentation). You can also override all these
    | settings per render.
    |
    */
    'show_warnings' => false,   // Throw an Exception on warnings from dompdf

    'public_path' => null,  // Override the public path if needed

    /*
     * Dejavu Sans font is used by default. Feel free to replace with your custom fonts.
     * More info: https://github.com/dompdf/dompdf/wiki/About-Fonts-and-Character-Encoding
     */
    'convert_entities' => true,

    'options' => [
        /**
         * The location of the DOMPDF font directory
         */
        'font_dir' => storage_path('fonts/'), // advised by dompdf (https://github.com/dompdf/dompdf/pull/782)

        /**
         * The location of the DOMPDF font cache directory
         */
        'font_cache' => storage_path('fonts/'),

        /**
         * The location of a temporary directory.
         */
        'temp_dir' => sys_get_temp_dir(),

        /**
         * ==== IMPORTANT ====
         *
         * dompdf's "chroot": Prevents dompdf from accessing system files or other
         * files on the webserver. All local files opened by dompdf must be in a
         * subdirectory of this directory. DO NOT set it to '/' since this could break
         * things if we're running on a webserver that allows local file access.
         */
        'chroot' => realpath(base_path()),

        /**
         * When enabled, dompdf will automatically evaluate embedded javascript code.
         * NOTE: This is a potential security risk.
         */
        'enable_javascript' => false,

        /**
         * This setting specifies whether CSS @import rules are followed.
         */
        'enable_css_float' => true,

        /**
         * If this setting is set to true, DOMPDF will use unicode character encoding.
         */
        'is_unicode' => true,

        /**
         * If this setting is set to true, DOMPDF will use the more-than-adequate SVG
         * renderer "Inline SVG" to render SVGs within HTML documents.
         */
        'is_html5_parser_enabled' => true,

        /**
         * If this setting is set to true, DOMPDF will use remote URLs.
         * IMPORTANT: Enabling this allows DOMPDF to make HTTP requests — needed for QR code images.
         */
        'is_remote_enabled' => true,

        /**
         * Allowed local file paths for dompdf.
         */
        'allowed_remote_hosts' => null,

        /**
         * A ratio applied to the fonts height to be more compatible with browsers.
         */
        'font_height_ratio' => 1.1,

        /**
         * Use the HTML5 parser (faster, handles most HTML correctly)
         */
        'dpi' => 96,

        /**
         * The PDF rendering backend to use.
         */
        'pdf_backend' => 'CPDF',

        /**
         * The default media type.
         */
        'default_media_type' => 'screen',

        /**
         * The default paper size.
         */
        'default_paper_size' => 'a4',

        /**
         * The default paper orientation.
         */
        'default_paper_orientation' => 'landscape',

        /**
         * The default font family
         */
        'default_font' => 'DejaVu Sans',

        /**
         * Image DPI setting
         */
        'image_dpi' => 96,

        /**
         * Enable inline PHP. Do not enable if you want to avoid code injection vulnerabilities.
         */
        'enable_php' => false,

        /**
         * Configuration for internal PDFLib
         */
        'pdflibLicense' => '',

        /**
         * HTTP context used to load remote content
         */
        'http_context' => null,
    ],
];
