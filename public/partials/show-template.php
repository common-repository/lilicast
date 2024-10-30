<?php
 /*
 Template name: Show Page
 */

global $post;

$plugin_uri = plugin_dir_url(__DIR__);
$the_ID = get_the_ID();

$header_font = get_post_meta($the_ID, 'lc-show-header_font', true);
$header_visible = get_post_meta($the_ID, 'lc-show-header_visible', true); // 'Tangerine'

if ($header_visible && $header_font) {
  $font = str_replace(' ', '+', $header_font);
  $url = "https://fonts.googleapis.com/css?family=". $font ."&display=swap";
  wp_enqueue_style( 'google-font', $url );
}

?>
<!doctype html>
<html lang="en">
  <head>
    <?php
      wp_head();
    ?>
  </head>
  <body <?php body_class(); ?>>
    <?php
  if ( post_password_required($post)) {
    ?>
      <div class="container">
        <div class="row justify-content-center">
          <?php
            echo get_the_password_form();
          ?>
        </div>
      </div>
    <?php
  } else {
    if ($header_visible) {
      $featured_img_url = get_the_post_thumbnail_url($the_ID,'full');?>
      <div class="jumbotron jumbotron-fluid" style="background-image: url(<?php echo $featured_img_url; ?>); background-size: cover; background-position: 50% 50%;">
        <div class="container lc-header">
          <?php
            $logo_img = get_post_meta($the_ID, 'lc-show-logo', true);
            if ($logo_img) {
              $logo_bg_color = get_post_meta($the_ID, 'lc-show-logo_bg_color', true);
              ?>
              <div class='lc-show-logo-bg' style="<?php if ($logo_bg_color) { echo 'background-color: ' . $logo_bg_color .'; border-color:' . $logo_bg_color . ';'; } ?>">
                <img src=<?php echo '"' . wp_get_attachment_url($logo_img) . '"'; ?> />
              </div>
            <?php
            };
            $show_name = get_post_meta($the_ID, 'lc-show-name', true);
            $header_text_color = get_post_meta($the_ID, 'lc-show-header_color', true);
            $header_bg_color = get_post_meta($the_ID, 'lc-show-header_bg_color', true);
            if ($show_name) { ?>
              <div class='lc-show-header-bg' style="<?php
                if ($header_bg_color) {
                  echo 'background-color: ' . $header_bg_color . ';';
                }
              ?>">
                <h1 class="display-4 lc-show-header"
                  style="<?php
                    if ($header_text_color) {
                      echo 'color: ' . $header_text_color . ';';
                    }
                    if ($header_font&&$header_font) {
                      echo 'font-family: ' . $header_font;
                    }
                  ?>">
                  <?php echo $show_name; ?>
                </h1>
              </div>
            <?php
            };
            $tagline = get_post_meta($the_ID, 'lc-show-tagline', true);
            if ($tagline) { ?>
              <div class='lc-show-tagline-bg' style="<?php
                if ($header_bg_color) {
                  echo 'background-color: ' . $header_bg_color . ';';
                }
              ?>">
                <p class="lead"
                  style="<?php
                    if ($header_text_color) {
                      echo 'color: ' . $header_text_color . ';';
                    }
                    if ($header_font&&$header_font) {
                      echo 'font-family: ' . $header_font;
                    }
                  ?>">
                  <?php echo $tagline; ?>
                </p>
              </div>
            <?php
            };
          ?>
        </div>
      </div>
    <?php }?>
      <!-- Borrowed from header.php starts -->
      <div id="page" class="site">
        <div class="site-content-contain">
          <div id="content" class="site-content lc-site-content">
            <!-- Borrowed from header.php ends -->
            <div class="wrap">
              <div id="primary" class="content-area">
                <main id="main" class="site-main" role="main">
                  <div class='container'>
                    <?php
                      $team_alignment = get_post_meta($the_ID, 'lc-show-team_alignment', true);
                      if (!isset($team_alignment)) {
                        $team_alignment = 'right';
                      }

                      $profile_container_class = '';
                      if ($team_alignment==='center') {
                        $profile_container_class = 'col-11 col-lg-8';
                      } else if ($team_alignment==='right') {
                        $profile_container_class = 'col-4';
                      }
                    ?>
                    <div class='row justify-content-center mb-5 lc-team-align-<?php echo $team_alignment; ?>'>
                      <div class="col-11 col-lg-8">
                        <?php

                          while ( have_posts() ) :
                            the_post();
                            the_content();
                            /* Do we ever want to render commnets?
                            if ( comments_open() || get_comments_number() ) :
                              comments_template();
                            endif;
                            */
                          endwhile; // End of the loop.
                        ?>
                      </div>
                      <?php

                        // When a single Show has been selected in the admin panel.
                        // Load some extra info about the Show to print the hosts.

                        $show_id = get_post_meta($the_ID, 'lc-show-show', true);
                        $this_show = null;
                        
                        if($show_id) {
                          $api_response = LilicastApiWrapper::get_shows();
                          $shows = $api_response['result'];

                          if (is_array($shows)) {
                            foreach ($shows as $key => $show) {
                              if ($show->id === $show_id) {
                                $this_show = $show;
                                break;
                              }
                            }
                          }
                        }?>
                    </div>
                  </div>
                  <div class='container'>
                    <div class='row text-center'>
                      <div class='col-3'>
                      </div>
                      <div class='col-6'>
                        <?php $lc_s = isset($_GET['lc_s']) ? sanitize_text_field($_GET['lc_s']) : ''; ?>
                        <div class="input-group mb-3">
                          <input type="text" class="form-control" id='search-input' placeholder="Search" aria-label="Search" aria-describedby="button-addon2" value="<?php echo $lc_s; ?>">
                          <div class="input-group-append">
                            <button class="btn btn-outline-secondary" type="button" id="button-addon2" onMousedown="search_cb()">
                              <svg
                                width="20"
                                height="20"
                                class='feather-svg'
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round">
                                <use xlink:href="<?php echo $plugin_uri . 'assets/feather/feather-sprite.svg#search';?>"/>
                              </svg>
                            </button>
                          </div>
                        </div>
                        <script>
                          var searchInput = document.getElementById('search-input');
                          var search_cb = function() {
                            // https://stackoverflow.com/a/487049/2641341
                            var kvp = document.location.search.substr(1).split('&');
                            var value = searchInput.value;
                            value = encodeURI(value);
                            var i=kvp.length; var x; 
                            while(i--) {
                              x = kvp[i].split('=');
                              if (x[0]=='lc_s') {
                                x[1] = value;
                                kvp[i] = x.join('=');
                                break;
                              }
                            }
                            if(i<0) {kvp[kvp.length] = ['lc_s',value].join('=');}
                            document.location.search = kvp.join('&'); 
                          }

                          searchInput.addEventListener('keydown', function(e) {
                            if (e.keyCode == 13) {
                              e.preventDefault();
                              search_cb();
                            };
                          })
                        </script>
                      </div>
                    </div>
                  </div>
                </main><!-- #main -->
              </div><!-- #primary -->
            </div><!-- .wrap -->
            <script>
              var scroll_to_video = function() { };
              <?php
                $scroll_to_video = get_post_meta($the_ID, 'lc-show-scroll_to_video', true);
                if ($scroll_to_video) { ?>
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
              <?php } ?>
            </script>
            <?php 

            // Render the [lilicast_flix_list]

            $shortcode_attrs = array();
            $shortcode_attrs['video_control_color']       = get_post_meta($the_ID, 'lc-show-video_control_color', true);
            $shortcode_attrs['video_control_hover_color'] = get_post_meta($the_ID, 'lc-show-video_control_hover_color', true);
            $shortcode_attrs['video_cover_bg_color']      = get_post_meta($the_ID, 'lc-show-video_cover_bg_color', true);
            $shortcode_attrs['video_cover_primary_color'] = get_post_meta($the_ID, 'lc-show-video_cover_primary_color', true);
            $shortcode_attrs['video_article_bg_color']    = get_post_meta($the_ID, 'lc-show-video_article_bg_color', true);
            $shortcode_attrs['video_article_title_color'] = get_post_meta($the_ID, 'lc-show-video_article_title_color', true);
            $shortcode_attrs['video_article_text_color']  = get_post_meta($the_ID, 'lc-show-video_article_text_color', true);
            $shortcode_attrs['video_control_color']       = get_post_meta($the_ID, 'lc-show-video_control_color', true);
            $shortcode_attrs['video_control_hover_color'] = get_post_meta($the_ID, 'lc-show-video_control_hover_color', true);
            $shortcode_attrs['video_progress_bar_color']  = get_post_meta($the_ID, 'lc-show-video_progress_bar_color', true);
            $shortcode_attrs['video_progress_bar_bg_color']  = get_post_meta($the_ID, 'lc-show-video_progress_bar_bg_color', true);
            $shortcode_attrs['video_article_highlight_color'] = get_post_meta($the_ID, 'lc-show-video_article_highlight_color', true);

            $shortcode_attrs['posts_per_page'] = get_post_meta($the_ID, 'lc-show-posts_per_page');
            // $shortcode_attrs['display_title'] = get_post_meta($the_ID, 'lc-show-posts_per_page')
            $shortcode_attrs['show_id'] = $show_id;

            $tags = get_post_meta($the_ID, 'lc-show-tags', true); 
            $tags = isset($tags[0]['tag']) ? $tags[0]['tag'] : null;
            $filters = array('categories' => 'lc-video', 'tags' => $tags); 
            
            $shortcode = new LilicastVideoGridNetflix($shortcode_attrs, $filters);

            $paged = get_query_var('paged') ? get_query_var('paged') : 1;
            $search_term = isset($_GET['lc_s']) ? sanitize_text_field($_GET['lc_s']) : '';

            echo $shortcode->render($paged, $search_term);
            ?>

            <?php get_footer(); ?>
          </div>
        </div>
      </div>
    <?php } /* end of else from if ( post_password_required($post)) { */?>
  </body>
</html>