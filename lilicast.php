<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              app.lilicast.com
 * @since             1.0.0
 * @package           Lilicast
 *
 * @wordpress-plugin
 * Plugin Name:       Lilicast
 * Plugin URI:        www.lilicast.com
 * Description:       Lilicast is the best way to easily spread radio content to the world! If you want to impact and increase the audience of your blog, activate the plugin to push your Lilicasts video straight to your WordPress media library.
 * Version:           2.1.10
 * Author:            LiLiCAST
 * Author URI:        app.lilicast.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       lilicast
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Warning: the version is actually taken from the above comment!
define( 'LILICAST_PLUGIN_NAME_VERSION', '2.1.10' );

/* For errors caused by calling files with absolute paths
 */
define( 'LILICAST_PLUGIN_FOLDER_NAME', basename(__DIR__)  );

/** Generated plugin specific constants
 */

// if ( ! defined( 'LILICAST_API_BASE' )) {
// 	// this is defined in the gulp build ... but if you bypass Gulp...
// 	// let's put the default to the QA endpoint.
// 	// Warning: Gulp changes the endpoint from the config to postpend /api/
// 	define( 'LILICAST_API_BASE', 'test.lilicast.com/api/');
// }

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-lilicast-activator.php
 */

register_activation_hook(
	__FILE__,
	function() {
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-lilicast-activator.php';
		Lilicast_Activator::activate();
	}
);

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-lilicast-deactivator.php
 */

register_deactivation_hook(
	__FILE__,
	function() {
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-lilicast-deactivator.php';
		Lilicast_Deactivator::deactivate();
	}
);

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-lilicast.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */

$run_lilicast = function() {
	$plugin = new Lilicast();
	$plugin->run();
};
$run_lilicast();
