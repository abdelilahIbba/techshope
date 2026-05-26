import { wayfinder } from '@laravel/vite-plugin-wayfinder';
import tailwindcss from '@tailwindcss/vite';
import react from '@vitejs/plugin-react';
import laravel from 'laravel-vite-plugin';
import path from 'path';
import { defineConfig } from 'vite';

export default defineConfig({
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
