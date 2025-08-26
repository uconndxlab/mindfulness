import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { copyFileSync, existsSync } from 'fs';
import { resolve } from 'path';

export default defineConfig({
    server: {
        host: 'mindfulness.test',
        port: 5173,
        hmr: {
            host: 'mindfulness.test',
        },
    },
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js'
            ],
            refresh: true,
        }),
        // copy PDF.js worker for CSP-compliant local hosting
        {
            name: 'copy-pdfjs-worker',
            writeBundle() {
                const srcPath = resolve('node_modules/pdfjs-dist/build/pdf.worker.mjs');
                const destPath = resolve('public/build/assets/pdf.worker.js');
                
                if (existsSync(srcPath)) {
                    copyFileSync(srcPath, destPath);
                    console.log('✓ PDF.js worker copied');
                }
            }
        }
    ],
});
