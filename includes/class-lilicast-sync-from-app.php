<?php

class LilicastSyncFromApp {

  public static function expandMemory(){
    $mem = intval(ini_get("memory_limit"));
    if($mem <= 64){
      error_log("Upgrading memory_limit to 256M. Was at ".ini_get("memory_limit"));
      ini_set("memory_limit","256M");
    }
  }

  // URL: /lilicast/v1/upload
  // POST JSON: 
  //   [postStatus] => draft
  //   [lilicastId] => 5eba61626910c053c7d68c5c
  public static function callback(WP_REST_Request $request){    
    error_log('------- Sync starting for lilicast id:' . $request['lilicastId']);
    // error_log(print_r($request, true));

    self::expandMemory();

    $api_response = LilicastApiWrapper::get_uploaded_lilicasts_to_wordpress();
    $lilicasts = $api_response['result'];
    
    if (gettype($lilicasts)==='array' && count($lilicasts) === 0) {
      $no_lilicasts_found_error = 'Error: No LiLiCASTS returned from the app API';
      error_log($no_lilicasts_found_error);
      return new WP_Error( 'lilicast_app_api_error', $no_lilicasts_found_error, array( 'status' => 400 ) );
    }

    if (isset($api_response['error_message'])) {
      return new WP_Error( 'unknown_error', $api_response['error_message'], array( 'status' => 500 ));
    } 

    if ($lilicasts) {
      $lilicast_found = false;
      foreach ($lilicasts as $key => $lc) {
        if ($request['lilicastId']===$lc->_id) {
            $lilicast_found = true;
            error_log('Found the LiLiCAST in Api response id: ' . $lc->_id . ' ' . in_array('wordpress', $lc->sharedOn));
            /* Load video
             */

            /* TODO:
             * - If video unsuccesfully uploaded (database has entity
             *   but has error code or does not have attachment), re-upload
             * - If video has been added already successfully, ping back,
             *   prompt and ask in the app if the action is still desired
             *   (re-upload with 'force' flag)
             */
            
            // TODO extract this into a method reusable for the `retry` logic.
            $vid_get = wp_remote_get($lc->videoUrl, array( 'timeout'=>120 ));

            if (is_wp_error($vid_get)) {
                $error_msg = 'Failed to get the video: ' . $lc->videoUrl . 'Aborting.';
                error_log($error_msg);
                error_log(print_r($vid_get, true));
                return new WP_Error( 'lilicast_video_error', $error_msg, array( 'status' => 500 ) );                
            } else if (is_array($vid_get)) {
                $response_code = wp_remote_retrieve_response_code($vid_get);
                $response_message = wp_remote_retrieve_response_message($vid_get);
                $vid_type = wp_remote_retrieve_header( $vid_get, 'content-type' );
                
                // error_log($response_code . ' ' . $response_message . ' ' . $vid_type);
                
                /* Handle basic issues with the video */
                if ($response_code != 200) {
                    self::update_database(null, $vid_id, $response_code, null, null, null );
                    return new WP_Error( 'lilicast_error', $response_message, array( 'status' => $response_code ) );
                };

                if (!$vid_type) {
                    /* Not sure if necessary to pass the last two null values */
                    self::update_database(null, $vid_id, 404, null, null, null, null);
                    return new WP_Error( 'lilicast_video_not_found', 'Video_not_found', array( 'status' => 404 ) );
                };

                error_log('Video has been loaded.');

                /* Upload video to WP media library (attachment) */
                $vid_attachment_id = self::upload_video_to_wp_media_library($lc, $vid_get);

                /* Get Thumbnail of video and assign to the attachment  */ 
                $vid_cover_attachment_id = self::download_and_assign_thumbnail_of_video($lc, $vid_attachment_id);

                /* Video attachment tag is used for rendering the video. */
                $vid_attachment_type = get_post_mime_type($vid_attachment_id);

                if ($vid_attachment_type==='application/octet-stream') {
                    $vid_attachment_type = 'video/mp4';
                }

                $vid_attachment_type_arr = explode('/', $vid_attachment_type);
                $vid_attachment_tag = '';

                if ($vid_attachment_type_arr[0]) {
                    $vid_attachment_tag .= '[' . $vid_attachment_type_arr[0];
                    $vid_attachment_tag .= ' ' . $vid_attachment_type_arr[1];
                    $vid_attachment_tag .= '="' . wp_get_attachment_url($vid_attachment_id) . '"';
                    $vid_attachment_tag .= '][/' . $vid_attachment_type_arr[0] . ']';
                }

                $post_status = sanitize_text_field($request['postStatus']);

                /* Iterate script, make content string out of it
                 */

                $vid_script = '';

                $replace_highlights = function ($text) {
                    $n_text = preg_replace('/ data-ids="[\s\S]+?"/', '', $text);
                    $n_text = preg_replace('/<lchl[\s\S]+?>/', '<lchl class="lc-highlight">', $text);
                    $n_text = str_replace('lchl', 'span', $n_text);
                    return $n_text;
                };

                if (isset($lc->sequence->items)) {
                  foreach ($lc->sequence->items as $key => $item) {
                    if ($item->item_type==='speech') {
                        $replace_highlights($item->content->text);
                        $vid_script = $vid_script . '<p class="lc-speech">' . $replace_highlights($item->content->text) . '</p>';
                    } else if ($item->item_type==='chat') {
                        foreach ($item->content->lines as $key => $line) {
                            $vid_script = $vid_script . '<p class="lc-chat">' . $replace_highlights($line->text) . '</p>';
                        }
                    } else if ($item->item_type==='music') {
                        $vid_script = $vid_script . '<p class="lc-music artist">' . $item->content->artist . '</p>';
                        $vid_script = $vid_script . '<p class="lc-music title">' . $item->content->title . '</p>';
                        $vid_script = $vid_script . '<p class="lc-music text">' . $replace_highlights($item->content->text) . '</p>';
                    }
                  }
                }

                $post = array(
                    'post_title' => $lc->title,
                    'post_content' => '<div class="lc-sc-article-video-wrapper"><div class="lc-sc-aspect-ratio">' . $vid_attachment_tag . '</div></div>' . $vid_script,
                    'post_status' => $post_status
                );

                $post_id = wp_insert_post( $post );


                /* Attach necessary metadata to post
                 */

                $id_obj = get_category_by_slug('lc-video');
                if (!isset($id_obj->term_id)) {
                    require_once(ABSPATH . 'wp-admin/includes/taxonomy.php');
                    error_log('Not existing, creating...');
                    wp_create_category( 'lc-video' );
                }

                wp_set_post_categories($post_id, array($id_obj->term_id));
                wp_set_post_categories($vid_attachment_id, array($id_obj->term_id));
                wp_set_post_tags($post_id, $lc->tags);
                update_post_meta($post_id, 'lilicast_article_for', $vid_attachment_id );
                update_post_meta($post_id, 'lilicast_show_id', $lc->showId);
                update_post_meta($post_id, 'lilicast_video_width', $vid_width);
                update_post_meta($post_id, 'lilicast_video_height', $vid_height);
                update_post_meta( $vid_attachment_id, 'lilicast_article_id', $post_id);

                self::update_database( $vid_attachment_id, $post_id, $lc->sequenceId, $response_code, $lc->title, $lc->startTime, $vid_cover_attachment_id );

                $response = new WP_REST_Response( array(
                    "id" => $vid_attachment_id,
                    "file_name" => $vid_file_name,
                    "type" => $vid_type,
                    "message" => 'Video started uploading!',
                ), 200 );
                
                error_log('------- Sync completed for lilicast id:' . $request['lilicastId']);

                return $response;
            }
        }
      } // Foreach end       

      if (!$lilicast_found) {
          $lilicast_not_found_error = 'Error: No LiLiCAST found';
          error_log('LiLiCAST was not found. Can not proceed');
          return new WP_Error( 'lilicast_app_api_error', $lilicast_not_found_error, array( 'status' => 400 ) );
      }
    }
  }

  // Alternatively: you can retry from the LiliCast App instead! 
  // $video_id is coming from LiLicast App
  // $id is the lilicast id in the wp_lilicast table.
  public static function retryVideoUpload($video_id, $id){
    // Retry Function ...
    // Don't maintain this ... instead: refactor LilicastSyncFromApp so you can easily retry the process ... The video is not the only thing that can fail ... thumbnail and post create can fail as well.
    
    $update_database = function($attachment_id, $lilicast_video_id, $response_code) use ($video_id, $id) {
        global $wpdb;
        $table_name = $wpdb->prefix . "lilicast";
        /* Change this to update function */
        $wpdb->update(
            $table_name,
            array(
                'time' => current_time( 'mysql' ),
                'response_code' => $response_code,
                'attachment_id' => $attachment_id
            ),
            array( 'id' => $id)
        );
    };

    $api_response = LilicastApiWrapper::get_video($video_id);

    if ($api_response['response_code'] != 200){
        $update_database(null, $video_id, $api_response['response_code']);
        echo '<script>(function(){     window.location.replace("?page=lilicast-top-level&retry=fail")    })()</script>';
        return;
    }
    
    $mirror = wp_upload_bits($api_response['file_name'], null, $api_response['body']);

    $attachment = array(
        'post_title'=> basename( $vid ),
        'post_mime_type' => $api_response['type']
    );

    $attachment_id = wp_insert_attachment( $attachment, $mirror['file']);

    update_post_meta( $attachment_id, 'lilicast_video_id', $video_id);
    $update_database( $attachment_id, $video_id, $api_response['response_code'] );
    echo '<script>(function(){     window.location.replace("?page=lilicast-top-level&retry=success")    })()</script>';
    
    return new WP_REST_Response( array(), 200 );
  }


  // -- private

  private static function download_and_assign_thumbnail_of_video($lc, $vid_attachment_id){
    if (isset($lc->videoThumbnails) && $lc->videoThumbnails[0]) {
      $vid_title = $lc->title;
      $vid_cover_url = $lc->videoThumbnails[0];
      $vid_cover_get = wp_remote_get($vid_cover_url);
      $vid_cover_type = wp_remote_retrieve_header($vid_cover_get, 'content-type');
      $vid_cover_file_name = self::get_upload_file_name($vid_title . 'cover', '.jpg');
      $vid_cover_body = wp_remote_retrieve_body($vid_cover_get);
      $vid_cover_mirror = wp_upload_bits($vid_cover_file_name, null, $vid_cover_body);
      $vid_cover_attachment = array(
          'post_title'     => $vid_title . '-cover',
          'post_mime_type' => 'image/jpeg'
      );
      $sizes = getimagesize($vid_cover_url);
      $vid_width = $sizes[0];
      $vid_height = $sizes[1];

      $vid_cover_attachment_id = wp_insert_attachment( $vid_cover_attachment, $vid_cover_mirror['file']);
      $vid_cover_attachment_url = get_attached_file($vid_cover_attachment_id);
      $vid_cover_meta = wp_generate_attachment_metadata($vid_cover_attachment_id, $vid_cover_attachment_url);

      wp_update_attachment_metadata( $vid_cover_attachment_id, $vid_cover_meta);
      update_post_meta($vid_cover_attachment_id, 'lilicast_cover_for', $vid_attachment_id);
      set_post_thumbnail($vid_attachment_id, $vid_cover_attachment_id);

      // TODO: Add error handling, if the loading fails?
      return $vid_cover_attachment_id;
    } else {
      error_log('Thumbnail not found, still proceeding. Check whats up with the API, though.');
    }
  }
  

  private static function update_database($attachment_id, $post_id, $lilicast_video_id, $response_code, $lilicast_video_title, $lilicast_video_start_time, $lilicast_cover_attachment_id) {
    global $wpdb;

    /* Helper for keeping LiLiCAST related data intact
     *
     * Actually, this function should receive just an object or array.
     * The amount of vars is hard to maintain when changes.
     */
    $table_name = $wpdb->prefix . "lilicast";

    $wpdb->insert(
        $table_name,
        array(
            'time' => current_time( 'mysql' ),
            'lilicast_video_id' => $lilicast_video_id,
            'lilicast_video_title' => $lilicast_video_title,
            'lilicast_video_start_time' => $lilicast_video_start_time,
            'cover_img_attachment_id' => $lilicast_cover_attachment_id,
            'response_code' => $response_code,
            'attachment_id' => $attachment_id,
            'post_id' => $post_id
        )
    );
  }

  // add video to the copy of the lilicast
  private static function upload_video_to_wp_media_library($lc, $vid_get){
    $vid_file_name = self::get_upload_file_name($lc->title, '.mp4');
    $vid_body = wp_remote_retrieve_body($vid_get);
    $vid_mirror = wp_upload_bits($vid_file_name, null, $vid_body);

    $vid_attachment = array(
        'post_title' => $vid_file_name,
        /* Not sure if forcing the mime type goes by the book
         * This makes it possible for the video to be showed properly on WP admin
         */
        'post_mime_type' => 'video/mp4'
    );

    $vid_attachment_id = wp_insert_attachment( $vid_attachment, $vid_mirror['file']);

    /* Add necessary metadata to attachment
     */

    wp_set_post_tags( $vid_attachment_id, $lc->tags);
    update_post_meta( $vid_attachment_id, 'lilicast_video_id', $lc->_id);
    $attach_data = wp_generate_attachment_metadata( $vid_attachment_id, $vid_mirror['file'] );
    wp_update_attachment_metadata( $vid_attachment_id, $attach_data );

    return $vid_attachment_id;
  }

  private static function get_upload_file_name($title, $extension) {
      $file_name = strip_tags($title); 
      $file_name = preg_replace('/[\r\n\t ]+/', ' ', $file_name);
      $file_name = preg_replace('/[\"\*\/\:\<\>\!\?\'\|]+/', '', $file_name);
      $file_name = strtolower($file_name);
      $file_name = html_entity_decode( $file_name, ENT_QUOTES, "utf-8" );
      $file_name = htmlentities($file_name, ENT_QUOTES, "utf-8");
      $file_name = preg_replace("/(&)([a-z])([a-z]+;)/i", '$2', $file_name);
      $file_name = str_replace(' ', '-', $file_name);
      $file_name = rawurlencode($file_name);
      $file_name = str_replace('%', '-', $file_name);
      $file_name = $file_name . $extension;
      error_log('Returning filename: ' . $file_name);
      return $file_name;
  }
}