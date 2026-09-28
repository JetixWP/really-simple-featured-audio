# JWP Audio Player

Source of the audio player bundled with Really Simple Featured Audio. It was
kept in the separate `JetixWP/jwp-audio-player-source` repository until 1.6.0.

The plugin loads the built files from `assets/`, not this folder. This folder
is not part of the release zip.

## Build

From the plugin root:

```
npm run build:player
```

This installs the player's own dependencies (Vite 2, kept apart from
`@wordpress/scripts`), builds it, and copies:

| Build output                   | Plugin file                         |
| ------------------------------ | ----------------------------------- |
| `dist/jwp-audio-player.min.js` | `assets/js/jwp-audio-player.min.js` |
| `dist/jwp-audio-player.cjs.js` | `assets/js/jwp-audio-player.js`     |
| `dist/style.css`               | `assets/css/jwp-audio-player.css`   |

Commit the updated files in `assets/` along with the source change. Keep
`package-lock.json` as is unless you mean to update the toolchain; it pins the
versions that reproduce the shipped files exactly.

## Credits and license

The player is based on [Shikwasa](https://github.com/jessuni/shikwasa) by
jessuni, released under the MIT License. Its copyright and permission notice
is kept in `LICENSE-shikwasa.md`, as that license requires.

Changes in this fork are released under GPL-3.0, see `LICENSE.md`.
