import js from '@eslint/js';
import globals from 'globals';

export default [
    {
        ignores: ['public/src/vendor/**', 'docs/**', 'vendor/**', 'node_modules/**']
    },
    js.configs.recommended,
    {
        files: ['public/src/js/**/*.js'],
        languageOptions: {
            ecmaVersion: 2022,
            sourceType: 'script',
            globals: {
                ...globals.browser,
                ...globals.jquery,
                bootstrap: 'readonly',
                Litepicker: 'readonly',
                API_URL: 'readonly'
            }
        }
    }
];
