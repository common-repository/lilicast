<?php
/* Original source: https://waclawjacek.com/check-wordpress-plugin-dependencies/
 */
class Lilicast_Plugin_Dependency_Exception extends Lilicast_Exception {

  /** @var string[] */
  private $missing_plugins;

  /**
   * @param string[] $missing_plugins Names of the plugins that our plugin depends on,
   *                                       that were found to be inactive.
   */
  public function __construct( $missing_plugins ) {
    $this->missing_plugins = $missing_plugins;
  }

  /**
   * @return string[]
   */
  public function get_missing_plugins() {
    return $this->missing_plugins;
  }

}