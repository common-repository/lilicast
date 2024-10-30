<script>
  var scroll_to_video = function() { };
  if (jQuery) {
    var jQuery_cb = function() {
      scroll_to_video = function(tgt) {
        jQuery('html, body').animate({
          scrollTop: jQuery(tgt).offset().top
        }, 300)
      }
    }
    jQuery_cb = jQuery_cb.bind(this)

    jQuery(document).ready( jQuery_cb );
  }
</script>

<script>
  var video_holders = document.getElementsByClassName("lc-video-holder");
  var progress_interval;
  var play_btn;
  var pause_btn;
  var fullscreen_btn;
  var video_progress_bar;

  for (var i = video_holders.length - 1; i >= 0; i--) {
    var video_holder = video_holders[i];
    var overlay = video_holder.querySelector("#lc-overlay");

    // Overlay actions
    overlay.addEventListener('mousedown', function(e) {
      // Cleanup old listeners
      if (window.lc_remove_play_btn_listener)            window.lc_remove_play_btn_listener();
      if (window.lc_remove_pause_btn_listener)           window.lc_remove_pause_btn_listener();
      if (window.lc_remove_fullscreen_listener)          window.lc_remove_fullscreen_listener();
      if (window.lc_remove_video_listeners)              window.lc_remove_video_listeners();
      if (window.lc_remove_jump_back_btn_listener)       window.lc_remove_jump_back_btn_listener();
      if (window.lc_remove_jump_fwd_btn_listener)        window.lc_remove_jump_fwd_btn_listener();
      if (window.lc_remove_video_progress_bar_listener)  window.lc_remove_video_progress_bar_listener();
      if (window.lc_remove_text_wrapper_scroll_listener) window.lc_remove_text_wrapper_scroll_listener();
      if (window.lc_remove_close_btn_listener)           window.lc_remove_close_btn_listener();
      clearTimeout(window.lc_video_set_size_timeout);

      // TODO: change the video_holder name, it is misleading now
      var this_video_holder = e.target.closest('.lc-video-holder');

      if (!this_video_holder.className.includes(' active')) {
        for (var i = video_holders.length - 1; i >= 0; i--) {
          video_holders[i].className = video_holders[i].className.replace(' active', ' ');
        }

        /* Make smarter state handling and cleanup
         */
        this_video_holder.className = this_video_holder.className + ' active';

        var this_content_holder = document.getElementById('content-holder-' + this_video_holder.dataset.postId);
        var content = get_content_box(this_video_holder.dataset.postId);
        this_content_holder.className = this_content_holder.className.replace(' loading', '');
        this_content_holder.className.replace(' hide-close-btn', '');
        this_content_holder.innerHTML = '';
        this_content_holder.appendChild(content);

        var play_btn = this_content_holder.querySelector("#lc-play");
        var pause_btn = this_content_holder.querySelector("#lc-pause");
        var jump_back_btn = this_content_holder.querySelector("#lc-jump-back");
        var jump_fwd_btn = this_content_holder.querySelector("#lc-jump-fwd");
        var fullscreen_btn = this_content_holder.querySelector("#lc-fullscreen");
        var video_progress_bar = this_content_holder.querySelector("#lc-video-progress");
        var video = this_content_holder.querySelector("#lc-video");
        var video_wrapper = this_content_holder.querySelector('.lc-video-wrapper');
        var text_wrapper = this_content_holder.querySelector('.lc-text-wrapper');
        var progress_bar = video_wrapper.querySelector("#lc-video-progress");
        var close_btn = this_content_holder.querySelector("#lc-close");

        // Play button actions

        var play_btn_cb = function() {
          video.play();
        }

        var pause_btn_cb = function() {
          video.pause();
        }

        this_content_holder.className = this_content_holder.className + ' loading';

        if (pause_btn&&play_btn) {
          pause_btn.addEventListener('mousedown', pause_btn_cb);
          play_btn.addEventListener('mousedown', play_btn_cb);
          window.lc_remove_pause_btn_listener = function() { pause_btn.removeEventListener('mousedown', pause_btn_cb)};
          window.lc_remove_play_btn_listener = function() { play_btn.removeEventListener('mousedown', play_btn_cb); }
        }

        // Jump button actions

        jump_back_cb = function() { 
          video.currentTime = video.currentTime - 5;
          progressBarUpdater(video_progress_bar, video);
        };
        jump_back_btn.addEventListener('mousedown', jump_back_cb);
        window.lc_remove_jump_back_btn_listener = function() { jump_back_btn.removeEventListener('mousedown', jump_back_cb); }

        jump_fwd_cb = function() {
          video.currentTime = video.currentTime + 5;
          progressBarUpdater(video_progress_bar, video);
        };
        jump_fwd_btn.addEventListener('mousedown', jump_fwd_cb);
        window.lc_remove_jump_fwd_btn_listener = function() { jump_fwd_btn.removeEventListener('mousedown', jump_fwd_cb); }

        var play_cb = function(e) {
          video_wrapper.setAttribute('data-playing', true);
          var progress_bar = video_wrapper.querySelector("#lc-video-progress");
          if (progress_interval) {
            clearInterval(progress_interval);
          }
          video.play();
          progress_interval = setInterval(function(){
            progressBarUpdater(progress_bar, video);
          }, 24);
        }

        var pause_cb = function(e) {
          video_wrapper.setAttribute('data-playing', false);
          video.pause();
          clearInterval(progress_interval);
        }

        var canplay_cb = function(e) {
          this_content_holder.className = this_content_holder.className.replace(' loading', '');
          var loader_elem = this_content_holder.querySelector('.lc-loader');
          if (loader_elem) {
            loader_elem.innerHTML = '';
          };
        }

        if (video) {
          video.addEventListener('play', play_cb);
          video.addEventListener('pause', pause_cb);
          video.addEventListener('loadedmetadata', canplay_cb);
          window.lc_remove_video_listeners = function() {
            video.removeEventListener('play', play_cb);
            video.removeEventListener('pause', pause_cb);
            video.removeEventListener('canplay', canplay_cb);
          }
        }

        // Fullscreen button actions

        var fullscreen_cb = function(e) {
          if (video.requestFullscreen) {
            video.requestFullscreen();
          } else if (video.mozRequestFullScreen) { /* Firefox */
            video.mozRequestFullScreen();
          } else if (video.webkitRequestFullscreen) { /* Chrome, Safari and Opera */
            video.webkitRequestFullscreen();
          } else if (video.msRequestFullscreen) { /* IE/Edge */
            video.msRequestFullscreen();
          }
        }

        if (fullscreen_btn) {
          fullscreen_btn.addEventListener('mousedown', fullscreen_cb);
          window.lc_remove_fullscreen_listener = function() { fullscreen_btn.removeEventListener('mousedown', fullscreen_cb); };
        }

        // Progress bar actions

        var progressBarUpdater = function(bar, srcVideo) {
          bar.firstElementChild.setAttribute('style', 'width:' + (srcVideo.currentTime/srcVideo.duration) * 100 + '%;' );
          bar.firstElementChild.setAttribute('aria-valuenow', (srcVideo.currentTime/srcVideo.duration) * 100);
        }

        var video_progress_bar_cb = function(e) {
          var boundRect = e.target.getBoundingClientRect();
          var relativeClientX = e.clientX - boundRect.x;
          var jumpRatio = relativeClientX / boundRect.width;
          video.currentTime = video.duration * jumpRatio;
          progressBarUpdater(progress_bar, video);

        }

        if (video_progress_bar) {
          video_progress_bar.addEventListener('mousedown', video_progress_bar_cb);
          window.lc_remove_video_progress_bar_listener = function() { video_progress_bar.removeEventListener('mousedown', video_progress_bar_cb); };
        }

        // Text area actions

        var text_wrapper_scroll_pos = 0;
        var text_scroll_cb = function(e) {
          if (e.target.scrollTop>text_wrapper_scroll_pos&&!this_content_holder.className.includes('hide-close-btn')) {
            this_content_holder.className = this_content_holder.className + ' hide-close-btn';
          } else if (e.target.scrollTop<text_wrapper_scroll_pos&&this_content_holder.className.includes('hide-close-btn')) {
            this_content_holder.className = this_content_holder.className.replace(' hide-close-btn', '');
          }
          text_wrapper_scroll_pos = e.target.scrollTop;
        }

        if (text_wrapper) {
          text_wrapper.addEventListener('scroll', text_scroll_cb);
          window.lc_remove_text_wrapper_scroll_listener = function() { text_wrapper.removeEventListener('scroll', text_scroll_cb); }
        }

        var close_cb = function() {
          pause_cb();
          this_video_holder.className = this_video_holder.className.replace(' active', ' ');
        }

        if (close_btn) {
          close_btn.addEventListener('mousedown', close_cb);
          window.lc_remove_close_btn_listener = function() { close_btn.removeEventListener('mousedown', close_cb); }
        }

        // Video resizing functions

        var video_set_size_cb = function () {
          /* The only purpose of this function is to set the size
           * of the video before the video is loaded. This prevents
           * the text content to "flash" before video exist.
           * Sizing of the grid should work without JS
           */
          var rect = video_wrapper.getBoundingClientRect();
          var wrap_height = rect.height;
          var height = this_video_holder.dataset.videoHeight
                       ? parseInt(this_video_holder.dataset.videoHeight)
                       : 180;
          var width = this_video_holder.dataset.videoWidth 
                      ? parseInt(this_video_holder.dataset.videoWidth)
                      : 320;

          hR = rect.height/height;
          wR = width/height; 
          var nHeight = height * hR;
          var nWidth = nHeight * wR;
          video.setAttribute('style', 'height:' + nHeight + 'px; width:' + nWidth + 'px;'); 
        }

        // Others

        scroll_to_video(video_wrapper);
      }


      if (video_set_size_cb)  video_set_size_cb();

      var video_set_size_timeout = function () {
        clearTimeout(window.lc_video_set_size_timeout);
        window.lc_video_set_size_timeout = setTimeout(video_set_size_cb, 200);
      }

      window.addEventListener('resize', video_set_size_timeout);
      window.lc_remove_video_resize_listener = function() { window.removeEventListener('resize', video_set_size_timeout); }
    }); // Overlay mouse down ends

  }
  var get_content_box = function(num) {
    var data = window.lc_page_data[num];
    if (data) {
      var movable_content_box = document.getElementById('lc-movable-content-box');
      var video_elem = movable_content_box.getElementsByTagName('video')[0];
      var text_elem = movable_content_box.getElementsByClassName('lc-text')[0];
      var title_elem = movable_content_box.getElementsByTagName('h1')[0];
      var length_elem = movable_content_box.getElementsByClassName('lc-length')[0];
      var date_elem = movable_content_box.getElementsByClassName('lc-date')[0];
      var loader_elem = movable_content_box.getElementsByClassName('lc-loader')[0];

      if (video_elem)   video_elem.setAttribute('src', data.video_src);
      if (text_elem)    text_elem.innerHTML = data.content;
      if (title_elem)   title_elem.innerHTML = data.post_title;
      if (length_elem)  length_elem.innerHTML = data.length_formatted;
      if (date_elem)    date_elem.innerHTML = data.date;
      if (loader_elem) {
        loader_elem.innerHTML = '<div><svg width="80" height="80" fill="none" stroke="currentColor" class="feather-svg" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use xlink:href="<?php echo $plugin_uri . 'assets/feather/feather-sprite.svg#loader';?>"/></svg></div>';
      }
    }

    return movable_content_box;
  };
</script>