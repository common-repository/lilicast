<?php
  
if( ! class_exists( 'WP_List_Table' ) ) {
  require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
};

/* 
 * List the imported lilicast. This is more for debugging than anything else.
 * It also shows a message with the API is not connected (dirty hack: should be outside). 
 *
 * Should be evaluated, if Lilicast_Table is needed after all
 * and if so, could it be abstracted better
 */
class Lilicast_Import_Table extends WP_List_Table {

    public $show_list;

    function get_columns(){
      $columns = array(
        'lilicast_video_title'      => 'Title',
        'lilicast_video_start_time' => 'Recording time',
        'response_code'             => 'Upload status',
        'lilicast_show_id'          => 'Show',
        'attachment_id'             => 'Media gallery ID'
      );
      return $columns;
    }

    function column_response_code($items) {
        if ($items['response_code']!='200') {
            echo '<span class="warning">Failed</span>';
        } else {
            echo '<span class="success">OK</span>';
        }
    }

    // Retry Btn or Show in gallery Btn
    function column_attachment_id($items) {
        $id = $items['id'];                       // Database id
        $video_id = $items['lilicast_video_id'];  // Video id
        $attachment_id = $items['attachment_id']; // Attachment (media gallery) id

        $connect_attributes = array(
            'onclick' => 'return (function(){ window.location.replace("' . admin_url() . 'upload.php?item=' . $attachment_id . '"); })();'
        );

        $retry_attributes = array(
            'onclick' => 'return (function(){ window.location.replace("?page=lilicast-top-level&retry_video_id=' . $video_id . '&id=' . $id . '"); })();'
        );

        if ($attachment_id) {
            submit_button( 'Show in media gallery', 'secondary', 'connect', false, $connect_attributes ); 
        } else {
            submit_button( 'Retry upload', 'primary', 'delete', false, $retry_attributes ); 
        }
    }

    function column_lilicast_video_start_time($items) {
      $start_time = $items['lilicast_video_start_time'];
      if ($start_time) {
        $date = new DateTime($start_time);
        $formatted = $date->format('d/m/Y H:i:s');
        echo $formatted;
      }
    } // Lilicast_Table ends

    // Join the Sync data with the API data to echo the Show Name.
    function column_lilicast_show_id ($items) {
      $id = $items['post_id'];
      $show_id = get_post_meta($id, 'lilicast_show_id', true);
      $show_name = '';
      foreach ($this->show_list as $key => $s) {
        if ($s->id === $show_id) {
          $show_name = $s->name;
          echo $s->name;
          return;
        }
      }

      echo $show_name;
    }

    function prepare_items() {
        global $wpdb;

        // Load from API for testing connection and to print the Show Name using the id

        $api_response = LilicastApiWrapper::get_shows();

        if (isset($api_response['error_message'])) {
          $this->show_list = [];
        } else {          
          $this->show_list = $api_response['result'];
        }

        // Load from WB DB from syncronised data.

        $lilicasts = $wpdb->get_results( 'SELECT * FROM ' . $wpdb->prefix . 'lilicast', ARRAY_A);
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = array();

        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->items = $lilicasts;
    }

    function column_default( $item, $column_name ) {
      switch( $column_name ) { 
        case 'lilicast_video_id':
        case 'lilicast_video_title':
        case 'lilicast_video_token':
        case 'lilicast_video_start_time':
        case 'lilicast_show_id';
        case 'attachment_id':
        case 'response_code':
          return $item[ $column_name ];
        default:
          return print_r( $item, true ) ; //Show the whole array for troubleshooting purposes
      }
    }
}