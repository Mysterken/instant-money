import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'

// https://vite.dev/config/
export default defineConfig({
  server: {
    port: +(process.env.NODE_PORT ?? 5173),
  },
  plugins: [react()],
})
