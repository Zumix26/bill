import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import vuetify from 'vite-plugin-vuetify';
import { fileURLToPath, URL } from 'url';

export default defineConfig({
    plugins: [
        vue(),
        vuetify({
            autoImport: true
        })
    ],
    server: {
        host: '0.0.0.0',
        port: 5173
    },
    resolve: {
        alias: {
            '@': fileURLToPath(new URL('./src', import.meta.url))
        }
    }
});

