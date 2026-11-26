import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            // Plain "border" / "divide-y" lines use the slate scale like the rest of the UI.
            borderColor: ({ theme }) => ({
                DEFAULT: theme('colors.slate.200'),
            }),
            colors: {
                // PNP navy: brand, navigation, primary actions.
                primary: {
                    50: '#F3F6FB',
                    100: '#E4EAF4',
                    200: '#C6D2E8',
                    300: '#9AAED3',
                    400: '#6781B6',
                    500: '#43609B',
                    600: '#2F4A82',
                    700: '#243C6C',
                    800: '#1A2F58',
                    900: '#0F2147',
                    950: '#081430',
                },
                // Gold: used sparingly for accents (active items, rules, labels).
                accent: {
                    50: '#FBF8EE',
                    100: '#F5EDD0',
                    200: '#EBDA9F',
                    300: '#DFC269',
                    400: '#D4AD43',
                    500: '#C49A2C',
                    600: '#A37D22',
                    700: '#7F611E',
                    800: '#654D1D',
                    900: '#53411C',
                },
            },
        },
    },

    plugins: [forms],
};
