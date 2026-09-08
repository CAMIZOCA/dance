/// <reference types="vitest/config" />
import { defineConfig, type Plugin } from 'vite'
import { readFileSync } from 'node:fs'
import react from '@vitejs/plugin-react'
import tailwindcss from '@tailwindcss/vite'

function updateWorkerFixturePlugin(): Plugin {
  return {
    name: 'e2e-update-worker-fixture',
    configurePreviewServer(server) {
      server.middlewares.use('/sw-update-fixture.js', (_request, response) => {
        response.setHeader('Content-Type', 'text/javascript; charset=utf-8')
        response.setHeader('Cache-Control', 'no-store')
        response.end(readFileSync(new URL('./e2e/fixtures/sw-update.js', import.meta.url), 'utf8'))
      })
    }
  }
}

export default defineConfig(({ mode }) => {
  return {
    plugins: [
      react(),
      tailwindcss(),
      ...(mode === 'e2e' ? [updateWorkerFixturePlugin()] : [])
    ],
    server: {
      proxy: {
        '/api': {
          target: 'http://127.0.0.1:8000',
          changeOrigin: true
        }
      }
    },
    test: {
      environment: 'jsdom',
      setupFiles: './src/test/setup.ts',
      globals: true,
      include: ['src/**/*.test.{ts,tsx}'],
      css: true
    }
  }
})
