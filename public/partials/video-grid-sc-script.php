<script>
  window.addEventListener('DOMContentLoaded', (event) => {
    var container = document.querySelector('.lc-sc-list-container');
    var vid_containers = document.querySelectorAll('.lc-sc-video-thumb-wrapper');

    var progressBarUpdater = function(bar, srcVideo) {
      bar.firstElementChild.setAttribute('style', 'width:' + (srcVideo.currentTime/srcVideo.duration) * 100 + '%;' );
      bar.firstElementChild.setAttribute('aria-valuenow', (srcVideo.currentTime/srcVideo.duration) * 100);
    }

    if (vid_containers!==null) {

      for (var i = vid_containers.length - 1; i >= 0; i--) {
        var vid_container = vid_containers[i];
        var overlay = vid_container.querySelector('.lc-sc-overlay');
        var video = vid_container.querySelector('video');
        var cover = vid_container.querySelector('.lc-sc-cover');
        var loader = vid_container.querySelector('.lc-loader');
        var controls = vid_container.querySelector('#lc-controls');
        var play_btn = vid_container.querySelector('#lc-play');
        var pause_btn = vid_container.querySelector('#lc-pause');
        var fullscreen_btn = vid_container.querySelector('#lc-fullscreen');
        var jump_back_btn = vid_container.querySelector('#lc-jump-back');
        var jump_fwd_btn = vid_container.querySelector('#lc-jump-fwd');
        var progress_bar = vid_container.querySelector('#lc-video-progress');

        overlay.container = vid_container;
        overlay.video = video;
        overlay.cover = cover;
        overlay.loader = loader;
        overlay.controls = controls;
        overlay.play_btn = play_btn;
        overlay.pause_btn = pause_btn;
        overlay.fullscreen_btn = fullscreen_btn;
        overlay.jump_back_btn = jump_back_btn;
        overlay.jump_fwd_btn = jump_fwd_btn;
        overlay.progress_bar = progress_bar;
        overlay.overlay = overlay; // Overlay element is only needed for initial play. Storing for later removal.

        var mousedown_cb = function (e) {
          this.video.play();
          this.loader.setAttribute('style', 'display: block;');
          this.overlay.parentNode.removeChild(this.overlay); // Destroy the DOM element, keep the object. Weird, I know.

          var canplay_cb = function() {
            this.loader.setAttribute('style', 'display: none;');
            this.loader.innerHTML = '';
            this.controls.setAttribute('style', 'display: block;');
          };

          canplay_cb = canplay_cb.bind(this);
          this.video.addEventListener('canplay', canplay_cb);

          var play_cb = function() {
            if (window.lc_sc_playing&&window.lc_sc_playing!==this.video) { 
              window.lc_sc_playing.pause()
            };

            window.lc_sc_playing = this.video;

            var progress_cb = function() {
              progressBarUpdater(this.progress_bar, this.video);
            };

            progress_cb = progress_cb.bind(this);
            this.progress_interval = setInterval(progress_cb, 24);

            this.container.setAttribute('data-playing', true);
          }

          play_cb = play_cb.bind(this);
          this.video.addEventListener('play', play_cb);

          var pause_cb = function() {
            clearInterval(this.progress_interval);;
            this.container.setAttribute('data-playing', false);
          }

          pause_cb = pause_cb.bind(this);
          this.video.addEventListener('pause', pause_cb);

          var play_btn_cb = function() {
            this.video.play();
          }

          play_btn_cb = play_btn_cb.bind(this);
          this.play_btn.addEventListener('mousedown', play_btn_cb);

          var pause_btn_cb = function() {
            this.video.pause();
          }

          pause_btn_cb = pause_btn_cb.bind(this);
          this.pause_btn.addEventListener('mousedown', pause_btn_cb);

          var jump_back_cb = function() {
            this.video.currentTime -= 5;
            progressBarUpdater(this.progress_bar, this.video);
          }

          jump_back_cb = jump_back_cb.bind(this);
          this.jump_back_btn.addEventListener('mousedown', jump_back_cb);

          var jump_fwd_cb = function() {
            this.video.currentTime += 5;
            progressBarUpdater(this.progress_bar, this.video);
          }

          jump_fwd_cb = jump_fwd_cb.bind(this);
          this.jump_fwd_btn.addEventListener('mousedown', jump_fwd_cb);

          var fullscreen_cb = function(e) {
            if (this.video.requestFullscreen) {
              this.video.requestFullscreen();
            } else if (this.video.mozRequestFullScreen) { /* Firefox */
              this.video.mozRequestFullScreen();
            } else if (this.video.webkitRequestFullscreen) { /* Chrome, Safari and Opera */
              this.video.webkitRequestFullscreen();
            } else if (this.video.msRequestFullscreen) { /* IE/Edge */
              this.video.msRequestFullscreen();
            }
          }

          fullscreen_cb = fullscreen_cb.bind(this);
          this.fullscreen_btn.addEventListener('mousedown', fullscreen_cb);

          var progress_bar_cb = function(e) {
            var boundRect = e.target.getBoundingClientRect();
            var relativeClientX = e.clientX - boundRect.x;
            var jumpRatio = relativeClientX / boundRect.width;
            this.video.currentTime = this.video.duration * jumpRatio;
            progressBarUpdater(this.progress_bar, this.video);
          };

          progress_bar_cb = progress_bar_cb.bind(this);
          this.progress_bar.addEventListener('mousedown', progress_bar_cb);
        }

        overlay.mousedown_cb = mousedown_cb.bind(overlay);
        overlay.addEventListener('mousedown', overlay.mousedown_cb);
      }
    }
  }); 
</script>