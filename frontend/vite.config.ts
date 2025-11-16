import { fileURLToPath, URL } from 'node:url';

import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import { quasar } from '@quasar/vite-plugin';
import postcssRTLCSS from 'postcss-rtlcss';

export default defineConfig({
  plugins: [
    vue(),
    quasar({
      sassVariables: 'src/quasar-variables.scss'
    })
  ],
  define: {
    'import.meta.env.VITE_API_URL': JSON.stringify('http://91.109.114.156:8000')
  },
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url)),
      src: fileURLToPath(new URL('./src', import.meta.url)),
      boot: fileURLToPath(new URL('./src/boot', import.meta.url)),
      '~vazirmatn': fileURLToPath(new URL('./node_modules/vazirmatn', import.meta.url)),
      'tinymce': fileURLToPath(new URL('./node_modules/tinymce', import.meta.url))
    }
  },
  css: {
    preprocessorOptions: {
      scss: {
        additionalData: '@import "@/quasar-variables.scss";'
      }
    },
    postcss: {
      plugins: [postcssRTLCSS()]
    }
  },
  server: {
    fs: {
      strict: false,
      allow: ['..']
    },
    cors: true,
    headers: {
      'Access-Control-Allow-Origin': '*',
      'Access-Control-Allow-Methods': 'GET,HEAD,PUT,PATCH,POST,DELETE',
      'Access-Control-Allow-Headers': 'Content-Type, X-XSRF-TOKEN, X-Requested-With'
    },
    proxy: {
      '/api': {
        target: 'http://91.109.114.156:8000',
        changeOrigin: true,
        secure: false,
        configure: (proxy, _options) => {
          proxy.on('error', (err, _req, _res) => {
            console.log('proxy error', err);
          });
          proxy.on('proxyReq', (proxyReq, req, _res) => {
            console.log('Sending Request to the Target:', req.method, req.url);
          });
          proxy.on('proxyRes', (proxyRes, req, _res) => {
            console.log('Received Response from the Target:', proxyRes.statusCode, req.url);
          });
        },
      }
    }
  }
});
