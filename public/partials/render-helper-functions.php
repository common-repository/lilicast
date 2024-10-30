<?php

  // /!\ Not Tested
  function get_host_photo_url($host) {
    if (isset($host->photoUrl)) {
      $photo_url = $host->photoUrl;
    } else {
      // Where would that asset comes from ?
      // Seems unecessary
      
      $directory = plugin_dir_path( __FILE__ ) . '../assets/profile-placeholders';
      $files = scandir($directory);
      $photo_idx = rand (2, count($files)-1);
      $photo_url = $plugin_uri . 'assets/profile-placeholders/' . $files[$photo_idx];
    } 

    return $photo_url;
  }
  
  function get_video_attr($post) {
    $attachment_tag_matches = array();
    $video_html_matches = array();
    preg_match('/\[video mp4=\"(.*?)\"/s', $post->post_content, $attachment_tag_matches);
    preg_match('/<video(.*?)><\/video>/s', $post->post_content, $video_html_matches);
    /* Resetting so that video does not 'bleed' to content element
     * which actually does not have a video
     */
    $video_src = null;
    $video = null;

    /* Not pretty, but does the job. Feel free to improve
     */

    if (isset($attachment_tag_matches[1])) {
      $video_src = $attachment_tag_matches[1];
    } else if (isset($video_html_matches[1])) {
      $src_matches = array();
      preg_match('/src=\"(.*?)\"/s', $video_html_matches[1], $src_matches);
      $video_src = isset($src_matches[1]) ? $src_matches[1] : null;
    }

     // hack to remove the https of the url in dev causing useless headhache... 
    if (isset($_ENV['ENV']) && $_ENV['ENV']==='dev') {
      $video_src = str_replace('https', 'http', $video_src);
    }

    return array(
      'video_src' => $video_src,
      'video'     => $video
    );
  }

  function get_vid_aspect_ratio($w, $h) {
    if ($w===$h) {
      $r = 'square';
    } else if ($w>$h) {
      $r = 'horisontal';
    } else if ($h>$w) {
      $r = 'portrait';
    }
    return $r;
  }

  function get_vid_sizes($attachment, $post_meta) {
    $width = null;
    $height = null;
    if (isset($attachment['width'])&&isset($attachment['height'])) {
      $width = $attachment['width'];
      $height = $attachment['height'];
    } else if (isset($post_meta['lilicast_video_width'])&&isset($post_meta['lilicast_video_height'])) {
      /* Using the logic of specifically saved video width and height
       * might be an overkill, just worried if the attachment aspect
       * ratios are reliable always.
       */
      $width = $post_meta['lilicast_video_width'][0];
      $height = $post_meta['lilicast_video_height'][0];
    }
    return array(
      'width' => $width,
      'height' => $height
    );
  }

  function clear_vid_tags($content) {
    /* TODO: Clumsy - come up with better solution */
    $content = preg_replace('/\[video[\s\S]+?video]/', '', $content);
    $content = preg_replace('/<div class="lc-sc-article-video-wrapper[\s\S]+?div>/', '', $content);
    $content = preg_replace('/<figure class="wp-block-video[\s\S]+?figure>/', '', $content);
    return $content;
  }
?>