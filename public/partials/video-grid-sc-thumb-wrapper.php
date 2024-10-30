<div class="lc-sc-video-thumb-wrapper" id="lc-sc-thumb-wrapper">
  <div class="lc-sc-aspect-ratio __ASPECT_RATIO__">
    <svg width="200" height="200" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
      <defs>
        <filter id="bg-blur-filter" x="0" y="0">
          <feGaussianBlur in="SourceGraphic" stdDeviation="18" />
        </filter>
      </defs>
      <image xlink:href="__COVER__" filter="url(#bg-blur-filter)"
             x="-50%" y="-50%" width="200%" height="200%"/>
    </svg>
    <img class="lc-sc-cover" src="__COVER__" />
    <video src="__VIDEO__" preload="none"></video>
    <div class="lc-loader">
      <div style="width: 20%; height: 20%;">
        <svg width="80" height="80" fill="white"
             stroke="white" class="feather-svg"
             stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <use xlink:href="__LOADER_ICON__"/>
        </svg>
      </div>
    </div>
    <div class="lc-sc-overlay">
      <div class='lc-sc-icon-play'>
        <svg
          width="80"
          height="80"
          fill="none"
          stroke="currentColor"
          class='feather-svg'
          stroke-width="2"
          stroke-linecap="round"
          stroke-linejoin="round">
          <use xlink:href="__PLAY_ICON__"/>
        </svg>
      </div>
    </div>
    <div class='lc-video-controls' id="lc-controls" style="display: none;">
      <div class='lc-center-controls'>
        <span class='break'></span>
        <div class='lc-action lc-jump-back' id='lc-jump-back'>
          <svg
            width="32"
            height="32"
            fill="none"
            stroke="currentColor"
            class='feather-svg'
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round">
            <use xlink:href="__JUMP_BACK_ICON__"/>
          </svg>
        </div>
        <div class='lc-action lc-play' id='lc-play'>
          <svg
            width="32"
            height="32"
            fill="none"
            stroke="currentColor"
            class='feather-svg'
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round">
            <use xlink:href="__PLAY_ICON__"/>
          </svg>
        </div>
        <div class='lc-action lc-pause' id='lc-pause'>
          <svg
            width="32"
            height="32"
            fill="none"
            stroke="currentColor"
            class='feather-svg'
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round">
            <use xlink:href="__PAUSE_ICON__"/>
          </svg>
        </div>
        <div class='lc-action lc-jump-fwd' id='lc-jump-fwd'>
          <svg
            width="32"
            height="32"
            fill="none"
            stroke="currentColor"
            class='feather-svg'
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round">
            <use xlink:href="__JUMP_FWD_ICON__"/>
          </svg>
        </div>
        <div class='lc-action lc-fullscreen' id='lc-fullscreen'>
          <svg
            width="32"
            height="32"
            fill="none"
            stroke="currentColor"
            class='feather-svg'
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round">
            <use xlink:href="__FULLSCREEN_ICON__" />
          </svg>
        </div>
      </div>
      <div class="progress" id="lc-video-progress">
        <div class="progress-bar" role="progressbar"aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
      </div>
    </div>
  </div>
  <span class="lc-sc-grid-timestamp">__TIMESTAMP__</span>
  <span class="lc-sc-grid-length">__LENGTH__</span>
  <div style="clear: both"></div>
  __TITLE__
  <a href="__PERMALINK__">
    <div class="lc-sc-excerpt">
      __CONTENT__
    </div>
  </a>
</div>