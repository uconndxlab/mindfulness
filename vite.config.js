import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { resolve } from 'path';
import { copyFileSync, existsSync, mkdirSync } from 'fs';

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
        // Custom plugin to copy PDF.js worker
        {
            name: 'copy-pdfjs-worker',
            buildStart() {
                // Ensure the build/assets directory exists
                const buildAssetsDir = resolve(process.cwd(), 'public/build/assets');
                if (!existsSync(buildAssetsDir)) {
                    mkdirSync(buildAssetsDir, { recursive: true });
                }
            },
            writeBundle() {
                // Copy PDF.js worker to build directory after build
                const workerSrc = resolve(process.cwd(), 'node_modules/pdfjs-dist/build/pdf.worker.mjs');
                const workerDest = resolve(process.cwd(), 'public/build/assets/pdf.worker.js');
                
                try {
                    if (existsSync(workerSrc)) {
                        copyFileSync(workerSrc, workerDest);
                        console.log('✓ PDF.js worker copied to build/assets/pdf.worker.js');
                    } else {
                        console.warn('⚠ PDF.js worker source not found at:', workerSrc);
                    }
                } catch (error) {
                    console.error('✗ Failed to copy PDF.js worker:', error);
                }
            }
        }
    ],
    worker: {
        format: 'es'
    },
});
