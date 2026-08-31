import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                navy: '#0A1628',
                cyan: {
                    DEFAULT: '#00D4AA',
                    500: '#00D4AA',
                    600: '#00bfa0',
                },
                orange: {
                    DEFAULT: '#FF6B35',
                    500: '#FF6B35',
                    600: '#e55a26',
                },
            },
        },
    },

    plugins: [forms, typography],
};
