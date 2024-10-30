<?php
/* Original source: https://waclawjacek.com/check-wordpress-plugin-dependencies/
 */
if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly
}

/** @var string[] $missing_plugins */
?>

<div class="notice-warning notice">
    <p>
        <strong>Warning:</strong>
        The <em>LiLiCAST</em> plugin won't work properly unless the following plugins are installed and active:
        <?php foreach ($missing_plugins as $plugin => $plugin_info) {
            echo  '<a href="' . $plugin_info['url'] . '" target="_blank">' . $plugin_info['plugin_name'] . '</a>';
        } ?>.
        Please activate these plugins.
    </p>
</div>