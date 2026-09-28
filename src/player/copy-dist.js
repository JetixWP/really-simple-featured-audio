/**
 * Copy the built player into the plugin's assets folder, under the names
 * the plugin loads.
 */
const fs = require('fs')
const path = require('path')

const dist = path.resolve(__dirname, 'dist')
const assets = path.resolve(__dirname, '../../assets')

const files = {
  'jwp-audio-player.min.js': 'js/jwp-audio-player.min.js',
  'jwp-audio-player.cjs.js': 'js/jwp-audio-player.js',
  'style.css': 'css/jwp-audio-player.css',
}

for (const [from, to] of Object.entries(files)) {
  const source = path.join(dist, from)

  if (!fs.existsSync(source)) {
    console.error(`Missing ${source}. Run the player build first.`)
    process.exit(1)
  }

  fs.copyFileSync(source, path.join(assets, to))
  console.log(`${from} -> assets/${to}`)
}
