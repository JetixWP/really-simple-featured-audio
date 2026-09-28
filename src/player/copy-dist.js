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

// The player is based on Shikwasa (MIT). Its notice must travel with the built files.
const notice = `JWP Audio Player (assets/js/jwp-audio-player*.js, assets/css/jwp-audio-player.css)

Based on Shikwasa, https://github.com/jessuni/shikwasa
Changes by JetixWP are released under GPL-3.0. Source: src/player in
https://github.com/JetixWP/really-simple-featured-audio

Shikwasa license:

${fs.readFileSync(path.resolve(__dirname, 'LICENSE-shikwasa.md'), 'utf8').trim()}
`

fs.writeFileSync(path.join(assets, 'js/jwp-audio-player-LICENSE.txt'), notice)
console.log('LICENSE-shikwasa.md -> assets/js/jwp-audio-player-LICENSE.txt')

for (const [from, to] of Object.entries(files)) {
  const source = path.join(dist, from)

  if (!fs.existsSync(source)) {
    console.error(`Missing ${source}. Run the player build first.`)
    process.exit(1)
  }

  fs.copyFileSync(source, path.join(assets, to))
  console.log(`${from} -> assets/${to}`)
}
