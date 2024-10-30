<?php

class LilicastApiWrapper {

  public static function api_base(){
    $api_select = get_option('lilicast_api_select');
    if ($api_select == 'qa') {
      error_log("Using QA API instead of default.");
      return 'test.lilicast.com/api/';
    } else {
      return 'api.lilicast.com/api/';
    }
  }

  public static function get_uploaded_lilicasts_to_wordpress(){    
    $api_url = 'https://' . self::api_base() . 'public/lilicasts?sharedOn=wordpress&withSequence=true';

    return self::get($api_url);
  }  

  public static function get_shows($limit=999) {
    $api_url = 'https://' .  self::api_base() . 'public/shows?limit=' . $limit;
    
    return self::get($api_url);
  }

  // TODO Diego both of these call are not found on the API.
  // Not Found
  public static function post_activate_plugin(){
    // $api_url = 'https://' .  self::api_base() . 'wordpress/plugin/activate';
    // $args = array(
    //   'method' => 'POST',
    //   'body'   => array(
    //     'url'  => get_site_url()
    //   )
    // );
    // // $lilicast_api_base . 'wordpress/plugin/activate'
    // $response = wp_remote_post($api_url, $args);
    // $error = self::build_error_message($response);
    // return !isset($error);
  }


  // Not Found
  public static function post_deactivate_plugin(){
    // $api_url = 'https://' .  self::api_base() . 'plugin/deactivate';
    // $args = array(
    //   'method' => 'POST',
    //   'body'   => array(
    //     'url'  => get_site_url()
    //   )
    // );
    // $response = wp_remote_post($api_url, $args);
    // $error = self::build_error_message($response);
    // return !isset($error);
  }

  public static function get_video($video_id) {
    $api_url = 'https://' .  self::api_base() . 'file/' . $video_id;
    
    $get = wp_remote_get($api_url);
    $type = wp_remote_retrieve_header( $get, 'content-type' );
    $response_code = wp_remote_retrieve_response_code($get);
    $body = wp_remote_retrieve_body($get);

    $file_name = 'lilicast-' . substr($vid, strrpos($vid, '/') + 1, strlen($vid)) . '.mp4';
    
    return array(
      'type' => $ype,
      'response_code' => $response_code,
      'file_name' => $file_name,
      'body' => $body,
    );
  }

  public static function getApiKey(){
    $api_key = get_option('lilicast_api_key');
    if(empty($api_key)){
      $previous_api_key = get_option('lc_api_key');
      if(!empty($previous_api_key)){
        error_log("API Key migrated from lc_api_key to lilicast_api_key");
        add_option('lilicast_api_key');
        update_option('lilicast_api_key', $previous_api_key); 
        $api_key = $previous_api_key;
      }
    }
    return $api_key;
  }

  // --- private

  private static function get($api_url){    
    $api_key = self::getApiKey();
    
    $headers = array(
      'Accept' => 'application/json',
      'X-Api-Key' => $api_key
    );
    
    $response = wp_remote_get( $api_url , array('headers' => $headers));
    
    $result = self::parse_json($response);
    $error_msg = self::build_error_message($response);

    if(isset($error_msg)){
      // error_log(print_r(gettype($response), true)); 
      // error_log(print_r($response, true));
    }
    
    return array(
      'result' => $result, 
      'error_message' => $error_msg
    );
  }

  // -- private

  private static function parse_json($response){
    $responseBody = wp_remote_retrieve_body( $response );
    
    if ( gettype($responseBody) === 'string' ) {
      return json_decode( $responseBody );
    }
  }

  private static function build_error_message($response){
    $http_code = wp_remote_retrieve_response_code($response);
    $responseBody = wp_remote_retrieve_body( $response );
    $error_messages = print_r($response, true);
    // error_log($error_messages);

    if ( is_wp_error( $response ) ) {
      return 'Error ('.$http_code.'):'. $error_messages;
    }
    elseif ( $http_code == 403 || $http_code == 401 ) {
      return "Forbidden Access ! Check your API KEY.";
    }
    elseif ( $http_code == 404 ) {
      return "Not Found." . $error_messages;
    }
    elseif ( $http_code == 500 ) {
      return "Application Error: 500." . error_messages;
    }
    elseif ( gettype($responseBody) === 'string' && strpos($responseBody, 'Error: Not Found')) {
      return "Not found. Either the app API is down or the endpoint has changed";
    }
  }
}

?>