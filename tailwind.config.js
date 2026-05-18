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
            colors: {
                hueco: {
                    yellow: '#FFD500',
                    green: '#3DD183',
                    cream: '#FCEFE6',
                    black: '#0A0A0A',
                    teal: '#00B8A5',
                },
            },
        },
    },

    plugins: [forms],
};
