import { defineConfig, loadEnv } from 'vite';
import laravel from 'laravel-vite-plugin';
import { copyFileSync, existsSync } from 'fs';
import { resolve } from 'path';

export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, process.cwd(), '');
    const devServerHost = env.VITE_DEV_SERVER_HOST || '127.0.0.1';
    const devServerPort = Number(env.VITE_DEV_SERVER_PORT || 5173);

    return {
        server: {
            host: devServerHost,
            port: devServerPort,
            hmr: {
                host: devServerHost,
                port: devServerPort,
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
    };
});
