import path from 'path'
import pkg from './package.json'

import { defineConfig } from 'vite'
import legacy from '@vitejs/plugin-legacy'

const CONSOLE_CODE = `console.log(\`%c🔊%c JWP Audio Player v${pkg.version} %c need help? Visit - https://jetixwp.com/contact\`,'background-color:#00869B40;padding:4px;','background:#00869B80;color:#fff;padding:4px 0','padding: 2px 0;')`

// Library build only. `npm run build:player` in the plugin root copies the
// files the plugin loads from dist/ into assets/.
export default defineConfig({
  define: { 'CONSOLE_MSG': CONSOLE_CODE },
  plugins: [{
    ...legacy({ targets: ['>0.2%', 'not ie <= 8'] }),
    apply: 'build',
  }],
  publicDir: false,
  build: {
    lib: {
      entry: path.resolve(__dirname, 'src/main.js'),
      name: 'JWP_Audio_Player',
      formats: ['cjs', 'es', 'umd', 'iife'],
      fileName: (format) => {
        const infix = format === 'umd' ? 'min' : format
        return `jwp-audio-player.${infix}.js`
      },
    },
  },
})
