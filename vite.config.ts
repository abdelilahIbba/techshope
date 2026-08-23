import { wayfinder } from '@laravel/vite-plugin-wayfinder';
import tailwindcss from '@tailwindcss/vite';
import react from '@vitejs/plugin-react';
import laravel from 'laravel-vite-plugin';
import path from 'path';
import { defineConfig } from 'vite';

export default defineConfig({
    server: {
        host: '0.0.0.0',
        port: 15173,
        strictPort: true,
        origin: 'http://localhost:15173',
        hmr: {
            host: 'localhost',
        },
    },
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.tsx'],
            ssr: 'resources/js/ssr.tsx',
            refresh: true,
        }),
        react({
            babel: {
                plugins: ['babel-plugin-react-compiler'],
            },
        }),
        tailwindcss(),
        wayfinder({
            formVariants: true,
        }),
    ],
    esbuild: {
        jsx: 'automatic',
    },
    server: {
        host: '0.0.0.0',
        port: 15173,
        strictPort: true,
        // Vite binds 0.0.0.0 inside the container so the port mapping works,
        // but that address isn't reachable from the browser — pin the origin
        // the browser should actually use so asset/HMR URLs stay correct.
        origin: 'http://localhost:15173',
        // The app is served from nginx on :18080, a different origin than
        // this dev server on :15173, so the browser needs Vite's explicit
        // go-ahead to fetch @vite/client and HMR updates cross-origin.
        cors: true,
    },
    resolve: {
        alias: [
            { find: /^@\/routes$/, replacement: path.resolve(__dirname, 'resources/js/routes-stubs/root.ts') },
            { find: '@/routes/two-factor/login', replacement: path.resolve(__dirname, 'resources/js/routes-stubs/two-factor-login.ts') },
            { find: '@/routes/two-factor', replacement: path.resolve(__dirname, 'resources/js/routes-stubs/two-factor.ts') },
            { find: '@/routes/verification', replacement: path.resolve(__dirname, 'resources/js/routes-stubs/verification.ts') },
            { find: '@/routes/appearance', replacement: path.resolve(__dirname, 'resources/js/routes-stubs/appearance.ts') },
            { find: '@/routes/user-password', replacement: path.resolve(__dirname, 'resources/js/routes-stubs/user-password.ts') },
            { find: '@/routes/profile', replacement: path.resolve(__dirname, 'resources/js/routes-stubs/profile.ts') },
            { find: '@/routes/register', replacement: path.resolve(__dirname, 'resources/js/routes-stubs/register.ts') },
            { find: '@/actions/App/Http/Controllers/Settings/ProfileController', replacement: path.resolve(__dirname, 'resources/js/routes-stubs/ProfileController.ts') },
            { find: '@/actions/App/Http/Controllers/Settings/PasswordController', replacement: path.resolve(__dirname, 'resources/js/routes-stubs/PasswordController.ts') },
            { find: '@', replacement: path.resolve(__dirname, 'resources/js') },
        ],
    },
});
