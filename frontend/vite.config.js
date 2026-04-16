import { defineConfig, loadEnv } from 'vite';
import react from '@vitejs/plugin-react';

export default defineConfig(({ mode }) => {
  const env = loadEnv(mode, process.cwd(), '');
  const backendUrl = env.VITE_BACKEND_URL || 'http://127.0.0.1:8000';

  return {
    plugins: [react()],
    build: {
      rollupOptions: {
        output: {
          manualChunks(id) {
            if (!id.includes('node_modules')) {
              return;
            }

            if (id.includes('/react/') || id.includes('react-dom') || id.includes('react-router-dom')) {
              return 'vendor';
            }

            if (id.includes('chart.js') || id.includes('react-chartjs-2')) {
              return 'charts';
            }

            if (id.includes('/axios/')) {
              return 'ui';
            }
          },
        },
      },
      chunkSizeWarningLimit: 600,
    },
    server: {
      port: 5173,
      proxy: {
        '/api': {
          target: backendUrl,
          changeOrigin: true,
        },
        '/sanctum': {
          target: backendUrl,
          changeOrigin: true,
        },
      },
    },
  };
});
