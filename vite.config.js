import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            // chat.js, research-submission.js and admin-charts.js are not
            // entries: resources/js/app.js imports them on demand, so Vite
            // splits them into chunks that only pages using them download.
            input: [
                'resources/css/app.css',
                'resources/css/layout.css',
                'resources/css/layouts/admin.css',
                'resources/css/layouts/home.css',
                'resources/css/layouts/instructor.css',
                'resources/css/layouts/public.css',
                'resources/css/layouts/repository.css',
                'resources/css/layouts/student.css',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
