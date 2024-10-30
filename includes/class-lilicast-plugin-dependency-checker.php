<?php
/* Original source: https://waclawjacek.com/check-wordpress-plugin-dependencies/
 */
class Lilicast_Plugin_Dependency_Checker {

  /**
   * Define the plugins that our plugin requires to function.
   * Array format: 'Plugin Name' => 'Path to main plugin file'
   */
  const REQUIRED_PLUGINS = array(
    array(
      'plugin_name' => 'Meta Box',
      'file_path'   => 'meta-box/meta-box.php',
      'url'         => 'https://wordpress.org/plugins/meta-box/'
    )
  );

  /**
   * Check if all required plugins are active, otherwise throw an exception.
   *
   * @throws My_Plugin_Name_Missing_Dependencies_Exception
   */
  public function check() {
    $missing_plugins = $this->get_missing_plugins_list();
    if ( ! empty( $missing_plugins ) ) {
      throw new Lilicast_Plugin_Dependency_Exception( $missing_plugins );
    }
  }

  /**
   * @return string[] Names of plugins that we require, but that are inactive.
   */
  private function get_missing_plugins_list() {
    $missing_plugins = array();
    foreach ( self::REQUIRED_PLUGINS as $plugin => $plugin_info ) {
      if ( ! $this->is_plugin_active( $plugin_info['file_path'] ) ) {
        $missing_plugins[] = $plugin_info;
      }
    }
    return $missing_plugins;
  }

  /**
   * @param string $main_file_path Path to main plugin file, as defined in self::REQUIRED_PLUGINS.
   *
   * @return bool
   */
  private function is_plugin_active( $main_file_path ) {
    return in_array( $main_file_path, $this->get_active_plugins() );
  }

  /**
   * @return string[] Returns an array of active plugins' main files.
   */
  private function get_active_plugins() {
    return apply_filters( 'active_plugins', get_option( 'active_plugins' ) );
  }

}