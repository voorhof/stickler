import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { google } from 'laravel-vite-plugin/fonts';
import replace from '@rollup/plugin-replace';

export default defineConfig({
    plugins: [
        laravel({
            refresh: true,
            input: ['resources/scss/app.scss', 'resources/js/app.js'],
            assets: ['resources/images/**'],
            fonts: [
                /*
                    google('Inter', {
                        alias: 'sans',
                        weights: [400, 600, 900],
                        styles: ['normal', 'italic'],
                        subsets: ['latin'],
                        display: 'swap',
                        preload: [
                            { weight: 400, style: 'normal' },
                            { weight: 600, style: 'normal' },
                        ],
                        fallbacks: ['system-ui', 'sans-serif'],
                    }),
                */
            ],
        }),
        replace({
            // Replace all 'data-bs' with 'data' in the build output
            preventAssignment: true,
            values: {
                'data-bs': 'data',
            },
        }),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
    // Silence Sass deprecation warnings from Bootstrap.
    // https://getbootstrap.com/docs/5.3/getting-started/vite/#configure-vite
    css: {
        preprocessorOptions: {
            scss: {
                api: 'modern-compiler',
                silenceDeprecations: [
                    'import',
                    // 'mixed-decls',
                    'color-functions',
                    'global-builtin',
                    'if-function',
                ],
            },
        },
    },
});
