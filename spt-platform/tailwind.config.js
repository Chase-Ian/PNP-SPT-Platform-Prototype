import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
        './resources/js/**/*.js',
    ],

    theme: {
        extend: {
            fontFamily: {
                // Bundled with the app (no font CDN), so it also works offline.
                sans: ['"Source Sans 3 Variable"', '"Source Sans 3"', ...defaultTheme.fontFamily.sans],
                mono: ['ui-monospace', 'SFMono-Regular', 'Menlo', 'Consolas', 'monospace'],
            },
            // Type scale: metadata 13px, labels/tables/body 15px, section titles 18-20px.
            fontSize: {
                xs: ['13px', { lineHeight: '18px' }],
                sm: ['15px', { lineHeight: '22px' }],
                base: ['16px', { lineHeight: '24px' }],
                lg: ['18px', { lineHeight: '26px' }],
                xl: ['20px', { lineHeight: '28px' }],
                '2xl': ['24px', { lineHeight: '32px' }],
                '3xl': ['30px', { lineHeight: '38px' }],
            },
            colors: {
                // Headings and the brand tile.
                navy: {
                    700: '#1f3a68',
                    800: '#172d54',
                    900: '#0f2347',
                    950: '#0a1830',
                },
            },
            borderColor: ({ theme }) => ({
                DEFAULT: theme('colors.slate.200'),
            }),
            boxShadow: {
                card: '0 1px 2px 0 rgb(15 23 42 / 0.04), 0 1px 3px 0 rgb(15 23 42 / 0.05)',
                popover: '0 12px 32px -12px rgb(15 23 42 / 0.28), 0 2px 6px 0 rgb(15 23 42 / 0.08)',
            },
        },
    },

    plugins: [forms],
};
