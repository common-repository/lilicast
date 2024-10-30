<?php

/**
 * Fired during plugin deactivation
 *
 * @link       app.lilicast.com
 * @since      1.0.0
 *
 * @package    Lilicast
 * @subpackage Lilicast/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Lilicast
 * @subpackage Lilicast/includes
 * @author     Jaakko Karhu <jaakko@26lights.com>
 */

class Lilicast_Deactivator {

    /**
     * Short Description. (use period)
     *
     * Long Description.
     *
     * @since    1.0.0
     */
    public static function deactivate() {
        LilicastApiWrapper::post_deactivate_plugin();
    }
}
