<?php

// Is both used for the template and shortchodes.
// return html & video-grid-sc-thumb-wrapper.php combined with the 
// query results.

class LilicastVideoGrid {
  // This is actually the code to render the shortcode.
  // TODO refactor lilicast-public to use this. (Huge lines can be saved!)
  public static function render_basic($page, $wp_query){
    $html .= LilicastVideoGrid::get_video_grid($wp_query, $display_title);
    $html .= LilicastVideoGrid::html_pagination($page, $wp_query->max_num_pages);
  }

  // transform categories & tags from the users ($_GET) into 
  // array of categorie_ids and tags that exists in wp database
  public static function build_filters($attrs){    
    // load existing cats & tags from database

    $args = array(
        'orderby' => 'id',
        'hide_empty' => false,
    );

    $wp_categories = get_categories($args);
    $wp_tags = get_tags($args);

    // explode input string into array

    $desired_tags = null;
    $desired_categories = [];

    if (isset($attrs['categories'])) {
        $categories = $attrs['categories'];
        // error_log(print_r($categories, true));
        $desired_categories = array_map('trim', explode(',', $categories));
        // error_log(print_r($desired_categories, true));
        // error_log(gettype($desired_categories));
    }

    if (isset($attrs['tags'])) {
        $tags = $attrs['tags'];
        $desired_tags = array_map('trim', explode(',', $tags));
    }

    // tranform category names into category ids

    $cat_ids = [];

    foreach ($desired_categories as $cat) {
        foreach ($wp_categories as $wp_cat) {
            if (strtolower($wp_cat->name) == strtolower($cat)) {
                array_push($cat_ids, $wp_cat->cat_ID);
                break;
            };
        }
    }
    $desired = array(
        'categories' => $cat_ids,
        'tags' => $desired_tags
    );
    return $desired;
  }

  public static function build_url_to_show_all($attrs, $filters){
    $url_cats = '';
    $url_tags = '';
    $url_page_max = '';
    $url_display_title = '';

    if (isset($filters['categories']) && count($filters['categories'])>=0 ) { 
      $url_cats .= '&cats=' . implode(',', $filters['categories']); 
    }
    if (isset($filters['tags']) && count($filters['tags']) >=0 ) { 
      $url_tags .= '&tags=' . implode(',', $filters['tags']); 
    }
    if (isset($atts['page_max'])) { 
      $url_page_max = '&page_max=' . $atts['page_max']; 
    }
    if (isset($attrs['display_title'])) { 
      $url_display_title = '&display_title=1'; 
    }

    return home_url() . '/lc-all?is_lc=1' . $url_cats . $url_tags . $url_page_max . $url_display_title;
  }

  public static function wp_query($attrs, $filters, $page = 1){
    $args = array(
      'post_type'      => 'post',
      'numberposts'    => -1,
      'order'          => 'DESC',
      'posts_per_page' => isset($attrs['page_max']) ? $attrs['page_max'] : 9,
      'paged'          => $page,
      'cat'            => $filters['categories'],
      'tag'            => $filters['tags'],
    );
    error_log(print_r($args, true));
    
    $wp_query = new WP_Query($args);
    return $wp_query;
  }

  public static function get_video_grid($the_query, $attrs) {
    $html = '';
    $plugin_uri=plugin_dir_url(__DIR__);
    if ( $the_query->have_posts() ) {
      $count = 0;
      $html .= "<div class='lc-sc-list-container'><div class='lc-sc-distributor'>";
      $html .= file_get_contents( plugin_dir_path( dirname( __FILE__ ) ) . 'public/partials/video-grid-sc-scoped-style.php');
        while ( $the_query->have_posts()) : $the_query->the_post();
          $post = get_post();
          $post_id = $post->ID;
          $video_attr = get_video_attr($post);
          $video_src = $video_attr['video_src'];
          if ($video_src) :
            $post_meta = get_post_meta($post->ID);
            $attachment_id = attachment_url_to_postid($video_src);
            $attachment = wp_get_attachment_metadata($attachment_id);
            $cover = get_the_post_thumbnail_url($attachment_id, 'medium_large');

            $vid_sizes = get_vid_sizes($attachment, $post_meta);
            $vid_width = $vid_sizes['width'];
            $vid_height = $vid_sizes['height'];

            $aspect_ratio = '';
            if ($vid_width&&$vid_height) {
              $aspect_ratio = get_vid_aspect_ratio($vid_width, $vid_height);
            };

            $length_formatted = is_array($attachment) ? $attachment['length_formatted'] : null;
            $permalink = get_permalink($post_id);
            $content = wp_strip_all_tags( $post->post_content );
            $content = clear_vid_tags($content);

            $html_template = file_get_contents( plugin_dir_path( dirname( __FILE__ ) ) . 'public/partials/video-grid-sc-thumb-wrapper.php');
            $html_template = str_replace('__ASPECT_RATIO__', $aspect_ratio, $html_template);
            $html_template = str_replace('__COVER__', $cover, $html_template);
            $html_template = str_replace('__VIDEO__', $video_src, $html_template);
            $html_template = str_replace('__LOADER_ICON__', $plugin_uri . 'public/assets/feather/feather-sprite.svg#loader', $html_template);
            $html_template = str_replace('__PLAY_ICON__', $plugin_uri . 'public/assets/feather/feather-sprite.svg#play', $html_template);
            $html_template = str_replace('__JUMP_BACK_ICON__', $plugin_uri . 'public/assets/feather/feather-sprite.svg#rotate-ccw', $html_template);
            $html_template = str_replace('__PAUSE_ICON__', $plugin_uri . 'public/assets/feather/feather-sprite.svg#pause', $html_template);
            $html_template = str_replace('__JUMP_FWD_ICON__', $plugin_uri . 'public/assets/feather/feather-sprite.svg#rotate-cw', $html_template);
            $html_template = str_replace('__FULLSCREEN_ICON__', $plugin_uri . 'public/assets/feather/feather-sprite.svg#maximize', $html_template);
            $html_template = str_replace('__TIMESTAMP__', get_the_date(null, $post_id), $html_template);
            $html_template = str_replace('__LENGTH__', $length_formatted ? $length_formatted : '', $html_template);

            if (isset($args['display_title'])) {
              $html_template = str_replace('__TITLE__', '<h4 class="lc-sc-grid-title">'  . $post->post_title . '</h4>', $html_template);
            } else {
              $html_template = str_replace('__TITLE__', '', $html_template);
            }

            $html_template = str_replace('__PERMALINK__', $permalink, $html_template);
            $html_template = str_replace('__CONTENT__', $content, $html_template);
            $html .= $html_template;
          endif;
          $count++;
        endwhile;
        $placeholder_count = ((floor($count / 3)) * 3 + 3) - $count;
        for ($i = 1; $i <= $placeholder_count; $i++) {
          $html .= "<div class='lc-sc-video-grid-placeholder'></div>";
        }
      $html .= "</div></div>";
      $html .= file_get_contents( plugin_dir_path( dirname( __FILE__ ) ) . 'public/partials/video-grid-sc-script.php');
    }
    return $html;
  } // get_video_grid

  public static function html_pagination($page, $max_num_pages){
    $html = '<div class="pagination lc-sc-pagination">';
    $pagination = paginate_links( array(
        'total'        => $max_num_pages,
        'current'      => max( 1, $page ),
        'format'       => '?&page=%#%',
        'show_all'     => false,
        'type'         => 'array',
        'end_size'     => 2,
        'mid_size'     => 1,
        'prev_next'    => true,
        'prev_text'    => sprintf( '<i></i> %1$s', __( 'Newer Posts', 'text-domain' ) ),
        'next_text'    => sprintf( '%1$s <i></i>', __( 'Older Posts', 'text-domain' ) ),
        'add_args'     => false,
        'add_fragment' => '',
    ));
    if (isset($pagination)) {                
        $pag_prev = '<div class="lc-sc-pagination-prev">';
        $pag_num = '<div class="lc-sc-pagination-num">';
        $pag_next = '<div class="lc-sc-pagination-next">';
        foreach ($pagination as &$elem) {
            if (strpos($elem, 'prev page-numbers') !== false) {
                $pag_prev .= $elem;
            } else if (strpos($elem, 'next page-numbers') !== false) {
                $pag_next .= $elem;
            } else {
                $pag_num .= $elem;
            }
        }
        $pag_prev .= '</div>';
        $pag_num .= '</div>';
        $pag_next .= '</div>';
        $html .= $pag_prev . $pag_num . $pag_next;
        $html .= '</div>'; // Close .pagination
    }
    return $html;
  }
}