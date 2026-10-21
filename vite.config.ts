import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import { existsSync, readFileSync } from 'node:fs';
import tailwindcss from "@tailwindcss/vite";
import { resolve } from 'node:path';
import { defineConfig } from 'vite';

const projectRoot = import.meta.dirname;

export default defineConfig({
    build: {
        cssMinify: 'esbuild',
    },
    plugins: [
        laravel({
            input: ['resources/js/app.ts'],
            ssr: 'resources/js/ssr.ts',
            refresh: true,
        }),
        tailwindcss(),
        vue({
            script: {
                fs: {
                    fileExists: existsSync,
                    readFile: (file) => {
                        try {
                            return readFileSync(file, 'utf-8');
                        } catch {
                            return undefined;
                        }
                    },
                },
            },
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    resolve: {
        alias: {
            '@': resolve(projectRoot, './resources/js'),
            'ziggy-js': resolve(projectRoot, 'vendor/tightenco/ziggy'),
        },
    },
});
