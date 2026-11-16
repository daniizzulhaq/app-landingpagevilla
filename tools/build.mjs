// Use Vite's native JS API to avoid config bundling restrictions on managed Windows.
import { build } from 'vite';
import laravel from 'laravel-vite-plugin';
await build({ configFile: false, plugins: [laravel({ input: ['resources/css/app.css', 'resources/js/app.js'] })] });
