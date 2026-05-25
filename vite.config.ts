import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite'; // <-- ADD THIS LINE

export default defineConfig({
    plugins: [
        tailwindcss(), // <-- ADD THIS PLUGIN HERE (before laravel())
        laravel({
            input: [
                "resources/css/app.css", 
                "resources/css/all.min.css",
                "resources/css/roboto.css",
                "resources/js/app.js",  
                "resources/js/vendor.js",
                "resources/js/meter.js",
                "resources/js/staff.js",
                "resources/js/superadmin.js",
            ],
            refresh: true,
        }),
    ],
});
