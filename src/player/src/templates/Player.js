const PlayerTemplate = /* template */ `
  <div class="jwp-player">
    <div class="jwp-bar_wrap">
      <div class="jwp-bar" aria-label="progress bar">
        <div class="jwp-bar_loaded"
          role="progressbar"
          aria-label="loaded progress"
          aria-valuenow="0"
          aria-valuemin="0"
          aria-valuemax="1"></div>
        <div class="jwp-bar_played"
          role="progressbar"
          aria-label="played progress"
          aria-valuenow="0"
          aria-valuemin="0"
          aria-valuemax="1">
          <button class="jwp-bar-handle"
            role="slider"
            aria-label="seek progress"
            aria-valuenow="0"
            aria-valuemin="0"
            aria-orientation="horizontal"
            aria-valuemax="1"></button>
        </div>
      </div>
    </div>
    <div class="jwp-body">
      <div class="jwp-main">
        <div class="jwp-header">
          <div class="jwp-cover">
            <div class="jwp-img"></div>
          </div>
          <div class="jwp-text">
            <div class="jwp-artist_wrap">
              <span class="jwp-artist"></span>
            </div>
            <div class="jwp-title_wrap">
              <div class="jwp-title_inner">
                <span class="jwp-title"></span>
              </div>
            </div>
          </div>
        </div>
        <div class="jwp-controls">
          <div class="jwp-controls_basic">
            <button class="jwp-btn jwp-btn_speed"
              aria-label="toggle playback rate"
              title="change playback rate"
              aria-live="polite">1.0x</button>
            <button class="jwp-btn jwp-btn_backward"
              aria-label="rewind 10 seconds"
              title="rewind 10 seconds">
              <svg aria-hidden="true">
                <use xlink:href="#jwp-icon_backward" />
              </svg>
            </button>
            <button class="jwp-btn jwp-btn_toggle" aria-label="toggle play and pause">
              <svg class="jwp-btn_play" aria-hidden="true">
                <use xlink:href="#jwp-icon_play" />
              </svg>
              <svg class="jwp-btn_pause" aria-hidden="true">
                <use xlink:href="#jwp-icon_pause" />
              </svg>
            </button>
            <button class="jwp-btn jwp-btn_forward" aria-label="forward 10 seconds" title="forward 10 seconds">
              <svg aria-hidden="true">
                <use xlink:href="#jwp-icon_forward" />
              </svg>
            </button>
            <button class="jwp-btn jwp-btn_more" aria-label="more controls" title="more controls">
              <svg aria-hidden="true">
                <use xlink:href="#jwp-icon_more" />
              </svg>
            </button>
          </div>
          <div class="jwp-controls_extra">
            <button class="jwp-btn jwp-btn_volume" aria-label="toggle volume" title="volume">
              <svg class="jwp-btn_unmute" aria-hidden="true">
                <use xlink:href="#jwp-icon_unmute" />
              </svg>
              <svg class="jwp-btn_mute" aria-hidden="true">
                <use xlink:href="#jwp-icon_mute" />
              </svg>
            </button>
          </div>
        </div>
        <div class="jwp-display">
          <span class="jwp-loader" aria-live="polite">
            <span class="jwp-visuallyhidden" tabindex="-1">loading</span>
            <svg aria-hidden="true" aria-label="loading" aria-live="polite" viewbox="0 0 66 66">
              <circle cx="33" cy="33" r="30" fill="transparent" stroke="url(#jwp-gradient)" stroke-dasharray="170"
                stroke-dashoffset="20" stroke-width="6" />
              <lineargradient id="jwp-gradient">
                <stop offset="50%" stop-color="currentColor" />
                <stop offset="65%" stop-color="currentColor" stop-opacity=".5" />
                <stop offset="100%" stop-color="currentColor" stop-opacity="0" />
              </lineargradient>
            </svg>
          </span>
          <span class="jwp-time">
            <span class="jwp-time_now">00:00</span><span class="jwp-time_duration">00:00</span>
          </span>
          <div class="jwp-live">live</div>
        </div>
      </div>
    </div>
  </div>
`
export default PlayerTemplate
