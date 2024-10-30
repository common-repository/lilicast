<div class='row no-gutters lc-video-grid'>
  <?php
    $frontend_data_posts = new stdClass(); 
    // TODO: Set error handling here for two cases: response fail and no posts
    if (count($lc_posts) > 0) {
      foreach ($lc_posts as $key => $lc_post) {

        $video_attr = get_video_attr($lc_post);
        $video_src = $video_attr['video_src']; 
        
        // error_log(print_r($video_attr['video_src'], true));k
        
        $video = $video_attr['video'];

        if ($video_src) {
          $lc_post_meta = get_post_meta($lc_post->ID);
          $attachment_id = attachment_url_to_postid($video_src);
          $cover = get_the_post_thumbnail_url($attachment_id, 'medium_large');
          $attachment = wp_get_attachment_metadata($attachment_id);

          $vid_sizes = get_vid_sizes($attachment, $lc_post_meta);
          $vid_width = $vid_sizes['width'];
          $vid_height = $vid_sizes['height'];

          $aspect_ratio = '';
          if ($vid_width && $vid_height) {
            $aspect_ratio = get_vid_aspect_ratio($vid_width, $vid_height);
          }
          ?>
          <div class="col-6 col-lg-4 col-xl-3 lc-video-holder"
            id="video-<?php echo $lc_post->ID;?>-holder"
            data-post-id="<?php echo $lc_post->ID; ?>"
            data-aspect-ratio="<?php echo $aspect_ratio; ?>"
            data-video-width="<?php echo $vid_width; ?>"
            data-video-height="<?php echo $vid_height; ?>">
            <?php echo $video; ?>
              <!-- TODO: add relevant alt tags to each image (SEO) -->
              <div class='lc-background'>
                <svg width="200" height="200" xmlns="http://www.w3.org/2000/svg"
                    xmlns:xlink="http://www.w3.org/1999/xlink">
                    <defs>
                      <filter id="bg-blur-filter" x="0" y="0">
                        <feGaussianBlur in="SourceGraphic" stdDeviation="18" />
                      </filter>
                    </defs>
                    <image xlink:href="<?php echo $cover; ?>"
                      filter="url(#bg-blur-filter)"
                      x="-50%" y="-50%" width="200%" height="200%"/>
                  <image xlink:href="<?php echo $cover; ?>"
                      x="0" y="0" width="100%" height="100%" />
                </svg>

                <svg width="200" height="200" xmlns="http://www.w3.org/2000/svg"
                    xmlns:xlink="http://www.w3.org/1999/xlink" class='lc-color-overlay'>
                  <defs>
                    <filter id="colorize-<?php echo $lc_post->ID; ?>">
                      <feColorMatrix
                            in="SourceGraphic"
                            type="matrix"
                            result="bw"
                            values="0 1 0 0 0
                                    0 1 0 0 0
                                    0 1 0 0 0
                                    0 1 0 1 0 "/>
                      <feFlood result="floodFill" x="0" y="0" width="100%" height="100%"
                          in="bw"
                          flood-color="<?php echo $video_cover_bg_color; ?>" flood-opacity="1"/>
                      <feBlend in="bw" mode="multiply"/>
                    </filter>
                  </defs>
                  <g style="filter:url(#colorize-<?php echo $lc_post->ID; ?>)">
                    <image xlink:href="<?php echo $cover; ?>"
                      filter="url(#bg-blur-filter)"
                      x="-50%" y="-50%" width="200%" height="200%"/>
                    <image xlink:href="<?php echo $cover; ?>"
                        x="0" y="0" width="100%" height="100%";"/>
                  </g>
                </svg>
                <div class='lc-gradient'
                  style='background: linear-gradient(0deg, <?php echo $video_cover_bg_color; ?>  0%, transparent 61%);'>
                </div>
                <div class='lc-arrow-wrapper'>
                  <div class='lc-arrow' style='border-top-color: <?php echo $video_cover_bg_color; ?>'></div>
                </div>
                <div class='lc-title-wrapper'>
                  <div class='lc-title-positioner'>
                    <h4 style='color:<?php echo $video_cover_primary_color; ?>'><?php echo $lc_post->post_title; ?></h4>
                  </div>
                </div>
              </div>
              <div class="lc-overlay" id="lc-overlay">
                <!-- registers events, don't remove -->
              </div>
          </div>
          <div class='lc-expander-probe'>
            <div class='lc-content-holder' id='content-holder-<?php echo $lc_post->ID; ?>'>
              <!-- Intentionally empty -->
            </div>
          </div>
          <?php
          $content = $lc_post->post_content;
          $content = clear_vid_tags($content);

          $frontend_post_obj = array(
            'ID' => $lc_post->ID,
            'video_src' => $video_src,
            'content' => $content,
            'post_title' => $lc_post->post_title,
            'date' => get_the_date(null, $lc_post->ID),
            'length_formatted' => is_array($attachment) ? $attachment['length_formatted'] : null
          );

          $ID = $lc_post->ID;
          $frontend_data_posts->$ID = $frontend_post_obj;
        } // end if ($video_src) {
      } // end foreach posts
      ?>
      <div class='lc-movable-video-content' id='lc-movable-content-box'>
        <div class='lc-video-wrapper'>
          <video src="" autoplay id='lc-video'></video>
          <div class='lc-video-controls'>
            <div class='lc-center-controls'>
              <style scoped>
                .lc-action {
                  position: relative; 
                  color: <?php echo $video_control_color; ?>;
                }
                .lc-action:hover { 
                  color: <?php echo $video_control_hover_color; ?>;
                }
              </style>
              <span class='break'></span>
              <div class='lc-action lc-jump-back' id='lc-jump-back'>
                <svg
                  width="52"
                  height="52"
                  fill="none"
                  stroke="currentColor"
                  class='feather-svg'
                  stroke-width="2"
                  stroke-linecap="round"
                  stroke-linejoin="round">
                  <use xlink:href="<?php echo $plugin_uri . 'assets/feather/feather-sprite.svg#rotate-ccw';?>"/>
                </svg>
              </div>
              <div class='lc-action lc-play' id='lc-play'>
                <svg
                  width="52"
                  height="52"
                  fill="none"
                  stroke="currentColor"
                  class='feather-svg'
                  stroke-width="2"
                  stroke-linecap="round"
                  stroke-linejoin="round">
                  <use xlink:href="<?php echo $plugin_uri . 'assets/feather/feather-sprite.svg#play';?>"/>
                </svg>
              </div>
              <div class='lc-action lc-pause' id='lc-pause'>
                <svg
                  width="52"
                  height="52"
                  fill="none"
                  stroke="currentColor"
                  class='feather-svg'
                  stroke-width="2"
                  stroke-linecap="round"
                  stroke-linejoin="round">
                  <use xlink:href="<?php echo $plugin_uri . 'assets/feather/feather-sprite.svg#pause';?>"/>
                </svg>
              </div>
              <div class='lc-action lc-jump-fwd' id='lc-jump-fwd'>
                <svg
                  width="52"
                  height="52"
                  fill="none"
                  stroke="currentColor"
                  class='feather-svg'
                  stroke-width="2"
                  stroke-linecap="round"
                  stroke-linejoin="round">
                  <use xlink:href="<?php echo $plugin_uri . 'assets/feather/feather-sprite.svg#rotate-cw';?>"/>
                </svg>
              </div>
              <div class='lc-action lc-fullscreen' id='lc-fullscreen'>
                <svg
                  width="52"
                  height="52"
                  fill="none"
                  stroke="currentColor"
                  class='feather-svg'
                  stroke-width="2"
                  stroke-linecap="round"
                  stroke-linejoin="round">
                  <use xlink:href="<?php echo $plugin_uri . 'assets/feather/feather-sprite.svg#maximize';?>"/>
                </svg>
              </div>
            </div>
            <style scoped>
              .progress { background-color: <?php echo $video_progress_bar_bg_color; ?>; }
              .progress-bar { background-color: <?php echo $video_progress_bar_color; ?>; }
            </style>
            <div class="progress" id="lc-video-progress">
              <div class="progress-bar" role="progressbar"aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
          </div>
          <div class="lc-loader"></div>
        </div>
        <div class='lc-text-wrapper'>
          <style scoped>
            .lc-text { 
              color: <?php echo $video_article_text_color; ?>; 
            }
            .lc-text-wrapper,
            .lc-close-btn:before { 
              background-color: <?php echo $video_article_bg_color; ?>; 
            }
            .lc-close-btn {
              color: <?php echo $video_control_color; ?>;
            }
            .lc-close-btn:hover { 
              color: <?php echo $video_control_hover_color; ?>;
            }
            .lc-text-wrapper h1 { 
              color: <?php echo $video_article_title_color; ?>; 
            }
            .lc-highlight { 
              <?php if ($video_article_highlight_color) { ?>
                background: <?php echo  $video_article_highlight_color; ?>;
                border-radius: 5px;
                padding-left: 2px;
                padding-right: 2px;
              <?php } ?>
            }
          </style>
          <div class='lc-close-btn' id='lc-close'>
            <svg
              width="30"
              height="30"
              class='feather-svg'
              fill="none"
              stroke="currentColor"
              stroke-width="2"
              stroke-linecap="round"
              stroke-linejoin="round">
              <use xlink:href="<?php echo $plugin_uri . 'assets/feather/feather-sprite.svg#x';?>"/>
            </svg>
          </div>
          <p class='lc-video-infos'>
            <span class='lc-date'></span>
            <span class='lc-length'></span>
          </p>
          <h1></h1>
          <div class='lc-text'></div>
        </div>
      </div>

    <?php 
      ob_start();
      include(plugin_dir_path( dirname( __FILE__ ) ) . 'partials/video-grid-netflix-script.php');
      echo ob_get_clean();
    } // end if(count($post) >0)
    else {
      echo file_get_contents( plugin_dir_path( dirname( __FILE__ ) ) . 'partials/video-grid-empty.php');
      // $html = include(plugin_dir_path( dirname( __FILE__ ) ) . 'partials/video-grid-empty.php');
      // echo rtrim($html, '1'); // remove a mysterious "1" from the include
    } 
    ?>

    <script>
      window.lc_page_data = <?php 
        try {
          echo json_encode($frontend_data_posts); 
        } catch(Exception $e) {
          error_log("Invalid JSON from lc_grid." . print_r($frontend_data_posts, true));
        }
      ?>;
    </script>

    <?php 
      $html = include(plugin_dir_path( dirname( __FILE__ ) ) . 'partials/video-grid-netflix-pagination.php');
      echo rtrim($html, '1'); // remove a mysterious "1" from the include
    ?>
</div> <!-- .lc-video-grid end -->