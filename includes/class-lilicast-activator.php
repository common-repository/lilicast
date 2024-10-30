<?php

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Lilicast
 * @subpackage Lilicast/includes
 * @author     Jaakko Karhu <jaakko@26lights.com>
 */

// for dbDelta();
require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

class Lilicast_Activator {

    public static function activate(){
        global $wpdb;

        $table_name = $wpdb->prefix . "lilicast"; 
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
          id                        mediumint(9) NOT NULL AUTO_INCREMENT,
          time                      datetime DEFAULT '0000-00-00 00:00:00' NULL,
          lilicast_video_id         varchar(255) NULL,
          attachment_id             mediumint(9) NULL,
          cover_img_attachment_id   mediumint(9) NULL,
          post_id                   mediumint(9) NULL,
          lilicast_video_title      varchar(255) NULL,
          lilicast_video_start_time varchar(255) NULL,
          response_code             smallint NULL,
          PRIMARY KEY  (id)
        ) $charset_collate;";

        dbDelta( $sql );

        LilicastApiWrapper::post_activate_plugin();
    }
}
