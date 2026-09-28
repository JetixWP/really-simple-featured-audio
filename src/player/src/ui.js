import PlayerComp from './templates/Player'
import IconComp from './templates/Icon'
import { secondToTime, numToString, marquee, createElement, toggleAttribute } from './utils'
import applyFocusVisible from './focus-visible'

let resize, coverUrl = null
let cooldown = true

export default class UI {
  constructor(options) {
    this.mounted = false
    this.icons = createElement({
      className: 'jwp-icons',
      innerHTML: IconComp,
    })
    this.initEl()
    this.initOptions(options)
  }

  async initEl() {
    this.el = createElement({
      className: ['jwp', 'JWP_Audio_Player'],
      attrs: {
        'data-name': 'JWP_Audio_Player',
      },
      innerHTML: PlayerComp,
    })
    this.playBtn = this.el.querySelector('.jwp-btn_toggle')
    this.fwdBtn = this.el.querySelector('.jwp-btn_forward')
    this.bwdBtn = this.el.querySelector('.jwp-btn_backward')
    this.speedBtn = this.el.querySelector('.jwp-btn_speed')
    this.moreBtn = this.el.querySelector('.jwp-btn_more')
    this.muteBtn = this.el.querySelector('.jwp-btn_volume')
    this.extraControls = this.el.querySelector('.jwp-controls_extra')
    this.texts = this.el.querySelector('.jwp-text')
    this.artist = this.el.querySelector('.jwp-artist')
    this.artistWrap = this.el.querySelector('.jwp-artist_wrap')
    this.titleWrap = this.el.querySelector('.jwp-title_wrap')
    this.titleInner = this.el.querySelector('.jwp-title_inner')
    this.title = this.el.querySelector('.jwp-title')
    this.currentTime = this.el.querySelector('.jwp-time_now')
    this.duration = this.el.querySelector('.jwp-time_duration')
    this.bar = this.el.querySelector('.jwp-bar')
    this.barWrap = this.el.querySelector('.jwp-bar_wrap')
    this.audioPlayed = this.el.querySelector('.jwp-bar_played')
    this.audioLoaded = this.el.querySelector('.jwp-bar_loaded')
    this.handle = this.el.querySelector('.jwp-bar-handle')
    this.cover = this.el.querySelector('.jwp-cover')
    this.seekControls = [this.fwdBtn, this.bwdBtn, this.handle]
  }

  initOptions(options) {
    this.el.setAttribute('data-theme', options.theme)

    // theme color
    if ( options.themeColor ) {
      this.el.style = `--color-primary: ${options.themeColor}`;
    }

    // download
    if (options.download && options.audio && options.audio.src) {
      this.downloadBtn = createElement({
        tag: 'a',
        className: ['jwp-btn', 'jwp-btn_download'],
        attrs: {
          title: 'download',
          'aria-label': 'download',
          href: typeof options.download === 'string' ? options.download : options.audio.src,
          download: '',
          target: '_blank',
          rel: 'noopener noreferrer',
        },
        innerHTML: /* html */ `
          <svg aria-hidden="true">
            <use xlink:href="#jwp-icon_download" />
          </svg>
        `,
      })
      this.extraControls.append(this.downloadBtn)
    }

    // player position
    this.el.setAttribute('data-fixed-type', options.fixed.type)
    if (options.fixed.type !== 'static' && options.fixed.position === 'top') {
      this.el.setAttribute('data-fixed-pos', options.fixed.position)
    }

    // player status display
    this.setPaused()
    if (options.autoPlay) {
      this.toggleAutoPlay()
    }

    // mute status display
    if (options.muted) {
      this.setMute(options.muted)
    }
  }

  initEvents(supportsPassive) {
    this.moreBtn.addEventListener('click', () => {
      toggleAttribute(this.el, 'data-extra')
    })
    Array.from(this.extraControls.children).forEach((el) => {
      this.hideExtraControl(el)
    })

    // add keyboard focus style
    applyFocusVisible(this.el, supportsPassive)

    resize = () => {
      if (!cooldown) return
      cooldown = false
      setTimeout(() => (cooldown = true), 100)
      marquee.call(this, this.titleWrap, this.title)
    }
    window.addEventListener('resize', resize)
  }

  setAudioInfo(audio = {}) {
    if (coverUrl) {
      URL.revokeObjectURL(coverUrl)
      coverUrl = null
    }
    if (/blob/.test(audio.cover)) {
      coverUrl = audio.cover
    }

    if (audio.cover) {
      this.cover.style.backgroundImage = `url(${audio.cover})`
    } else {
      this.cover.style.backgroundImage = 'var(--cover-img-url)'
    }
    this.title.innerHTML = audio.title
    this.titleInner.setAttribute('data-title', audio.title)
    this.artist.innerHTML = audio.artist
    if (audio.duration) {
      this.duration.innerHTML = secondToTime(audio.duration)
    }
    if (this.downloadBtn) {
      this.downloadBtn.href = audio.src
    }
    this.setBar('loaded', 0)
    this.setLive(audio.live)
    marquee(this.titleWrap, this.title)
  }

  setPlaying() {
    this.el.setAttribute('data-play', 'playing')
  }

  toggleAutoPlay() {
    setTimeout(function () {
      window.addEventListener('click', () => {
        if (window.JWP_Audio_Player_Instance._canplay) {
          window.JWP_Audio_Player_Instance.ui.setPlaying()
          window.JWP_Audio_Player_Instance.toggle()
        }
      }, { once: true })
    }, 100)
  }

  setPaused() {
    this.el.setAttribute('data-play', 'paused')
    this.setLoading(false)
  }

  setTime(type, time) {
    this[type].innerHTML = secondToTime(time)
  }

  setBar(type, percentage) {
    const typeName = 'audio' + type.charAt(0).toUpperCase() + type.substr(1)
    percentage = Math.min(percentage, 1)
    percentage = Math.max(percentage, 0)
    this[typeName].style.width = percentage * 100 + '%'
    const ariaNow = percentage.toFixed(2)
    this[typeName].setAttribute('aria-valuenow', ariaNow)
    this.handle.setAttribute('aria-valuenow', ariaNow)
  }

  setProgress(time = 0, percentage = 0, duration = 0) {
    if (time && !percentage) {
      percentage = duration ? time / duration : 0
    } else {
      time = percentage * (duration || 0)
    }
    this.setTime('currentTime', time)
    this.setBar('played', percentage)
  }

  setSpeed(speed) {
    this.speedBtn.innerHTML = numToString(speed) + 'x'
  }

  setMute(mute) {
    toggleAttribute(this.el, 'data-mute', mute)
  }

  setLive(live = false) {
    toggleAttribute(this.el, 'data-live', live)
  }

  setLoading(loading) {
    toggleAttribute(this.el, 'data-loading', loading)
  }

  setSeeking(seeking) {
    toggleAttribute(this.el, 'data-seeking', seeking)
  }

  setControls(allowControl) {
    this.seekControls.forEach((el) => {
      toggleAttribute(el, 'disabled', !allowControl)
    })
  }

  getPercentByPos(e) {
    const handlePos = e.clientX || (e.changedTouches && e.changedTouches[0].clientX) || 0
    const initPos = this.barWrap.getBoundingClientRect().left
    const barLength = this.barWrap.clientWidth
    let percentage = (handlePos - initPos) / barLength
    percentage = Math.min(percentage, 1)
    percentage = Math.max(0, percentage)
    return percentage
  }

  hideExtraControl(el) {
    el.addEventListener('click', () => {
      setTimeout(() => {
        this.el.removeAttribute('data-extra')
      }, 800)
    })
  }

  mount(container, supportsPassive) {
    container.innerHTML = ''
    container.append(this.el)
    if (this.icons) {
      container.append(this.icons)
    }
    this.mounted = true
    this.initEvents(supportsPassive)
    marquee(this.titleWrap, this.title)
  }

  destroy() {
    window.removeEventListener('resize', resize)
    if (coverUrl) {
      URL.revokeObjectURL(coverUrl)
    }
  }
}
