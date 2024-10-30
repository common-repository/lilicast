<?php
/* Original source: https://waclawjacek.com/check-wordpress-plugin-dependencies/
 */
class Lilicast_Plugin_Dependency_Reporter {

  const REQUIRED_CAPABILITY = 'activate_plugins';

  /** @var string[] */
  private $missing_plugins;

  /**
   * @param string[] $missing_plugins
   */
  public function __construct( $missing_plugins ) {
    $this->missing_plugins = $missing_plugins;
  }

  public function bind_to_admin_hooks() {
    add_action( 'admin_notices', array( $this, 'display_admin_notice' ) );
  }

  public function display_admin_notice() {
    if ( ! current_user_can( self::REQUIRED_CAPABILITY ) ) {
      // If the user does not have the "activate_plugins" capability, do nothing.
      return;
    }

    $missing_plugins = $this->missing_plugins;
    include dirname( __FILE__ ) . '/../admin/partials/lilicast-missing-dependencies-admin-notice.php';
  }

}