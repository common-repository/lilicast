<?php 

class LilicastVideoGridNetflix {
  public function __construct($shortcode_attrs, $filters) {
    $this->shortcode_attrs = $shortcode_attrs;
    $this->filters = $filters;

    $this->show_id = isset($shortcode_attrs['show_id']) ? $shortcode_attrs['show_id'] : null; 
    $this->posts_per_page = isset($shortcode_attrs['posts_per_page']) ? $shortcode_attrs['posts_per_page'] : 36;
    $this->display_title = isset($shortcode_attrs['display_title']); // unused
  }

  public function render($page = 1, $search_term = null){
    $lc_postslist = $this->wp_query($page, $search_term);
    $paged = $page;
    // error_log(print_r($this->shortcode_attrs, true));

    // shares vars with the partials
    $lc_posts = $lc_postslist->posts;
    $display_title = $this->display_title;
    $show_id = $this->show_id;
  
    // sorry for the long code, but it's always a (isset ? value : null);
    $video_control_color       = (isset($this->shortcode_attrs['video_control_color'])       ? $this->shortcode_attrs['video_control_color']       : 'whitesmoke');
    $video_control_hover_color = (isset($this->shortcode_attrs['video_control_hover_color']) ? $this->shortcode_attrs['video_control_hover_color'] : '#1e73be');
    $video_cover_bg_color      = (isset($this->shortcode_attrs['video_cover_bg_color'])      ? $this->shortcode_attrs['video_cover_bg_color']      : '#1e73be');
    $video_cover_primary_color = (isset($this->shortcode_attrs['video_cover_primary_color']) ? $this->shortcode_attrs['video_cover_primary_color'] : 'whitesmoke');
    $video_article_bg_color    = (isset($this->shortcode_attrs['video_article_bg_color'])    ? $this->shortcode_attrs['video_article_bg_color']    : '#212025');
    $video_article_title_color = (isset($this->shortcode_attrs['video_article_title_color']) ? $this->shortcode_attrs['video_article_title_color'] : 'white');
    $video_article_text_color  = (isset($this->shortcode_attrs['video_article_text_color'])  ? $this->shortcode_attrs['video_article_text_color']  : 'whitesmoke');
    $video_control_color       = (isset($this->shortcode_attrs['video_control_color'])       ? $this->shortcode_attrs['video_control_color']       : 'whitesmoke');
    $video_control_hover_color = (isset($this->shortcode_attrs['video_control_hover_color']) ? $this->shortcode_attrs['video_control_hover_color'] : '#1e73be');
    $video_progress_bar_color  = (isset($this->shortcode_attrs['video_progress_bar_color'])  ? $this->shortcode_attrs['video_progress_bar_color']  : '#1e73be');
    $video_progress_bar_bg_color = (isset($this->shortcode_attrs['video_progress_bar_bg_color']) ? $this->shortcode_attrs['video_progress_bar_bg_color'] : 'whitesmoke');
    $video_article_highlight_color = (isset($this->shortcode_attrs['video_article_highlight_color']) ? $this->shortcode_attrs['video_article_highlight_color'] :  null);

    $plugin_uri = plugin_dir_url(__DIR__) . '/public/';

    ob_start();
    include(plugin_dir_path( dirname( __FILE__ ) ) . 'public/partials/video-grid-netflix-template.php');
    $html = ob_get_clean();

    return rtrim($html, '1'); // remove a mysterious "1" from the include
  }

  private function wp_query($page, $search_term){
    $meta_query = array();
    if ($this->show_id) {
      array_push($meta_query, array(
        'key' => 'lilicast_show_id',
        'value' => $this->show_id,
        'compare' => 'LIKE'
      ));
    }

    $query_args = array(
      'post_type'      => 'post',
      'order'          => 'DESC',
      'posts_per_page' => $this->posts_per_page,
      'paged'          => $page,
      'cat'            => $this->filters['categories'],
      'tag'            => $this->filters['tags'],
      'meta_query'     => $meta_query
    );

    if ($search_term) {
      $query_args['s'] = sanitize_text_field($search_term);
    }

    // error_log(print_r($query_args, true));

    return new WP_Query($query_args);
  }
}
?>