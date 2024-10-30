<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       app.lilicast.com
 * @since      1.0.0
 *
 * @package    Lilicast
 * @subpackage Lilicast/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Lilicast
 * @subpackage Lilicast/admin
 * @author     Jaakko Karhu <jaakko@26lights.com>
 */

if( ! class_exists( 'WP_List_Table' ) ) {
  require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
};

class Lilicast_Admin {

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $plugin_name       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct( $plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;

        /* Clumsy sniffing for the editor which is used. We don't want to
         * alter the save functions of Gutenberg and Meta Box, since they
         * are working fine.
         */
        $this->is_gutenberg = false;
        /* Actually, this should be turned the other way around and be
         * $is_classic. The conditonal tests more that.
         */
        if (!isset( $_POST['post_author_override'])&&!isset($_POST['meta'])) {
          // Editor is most likely Gutenberg.
          $this->is_gutenberg = true;
        }
    }

    public function add_admin_menu() {
      require_once plugin_dir_path( __FILE__ ) . '../admin/partials/lilicast-admin-settings.php';
      require_once plugin_dir_path( __FILE__ ) . '../admin/partials/lilicast-admin-generate-shortcode.php';
      require_once plugin_dir_path( __FILE__ ) . '../admin/partials/lilicast-admin-generate-netflix-shortcode.php';
      
      add_submenu_page( 'lilicast-top-level', 'Overview', 'Overview',
          'manage_options', 'lilicast-top-level');
    }

    public function register_settings() {
      register_setting('overview_settings_group', 'lilicast_api_key');
      register_setting('overview_settings_group', 'lilicast_api_select');
    }

    public function lilicast_upload_retry_fail() {
      if (isset($_GET['retry'])&&$_GET['retry']=='fail') {
      ?>
        <div class="notice notice-warning is-dismissible">
            <h4>Upload failed.</h4>
            <p>Are you sure the video still exists on Lilicast app?</p>
        </div>
      <?php
      }
    }

    public function lilicast_upload_retry_success() {
      if (isset($_GET['retry'])&&$_GET['retry']=='success') {
      ?>
        <div class="notice is-dismissible">
            <h4>Video uploaded succesfully!</h4>
        </div>
      <?php
      }
    }

    public function on_delete_attachment($id) {
      global $wpdb;
      $attachment = $wpdb->get_results(
        "
          DELETE
          FROM " . $wpdb->prefix . "lilicast
          WHERE attachment_id = $id
        ",
        ARRAY_A); 
    }

    public function add_categories_to_attachments() {
      register_taxonomy_for_object_type( 'category', 'attachment' );
    }

    public function add_tags_to_attachments() {
      register_taxonomy_for_object_type( 'post_tag', 'attachment' );
    }

    public function remove_lc_custom_fields($protected, $meta_key) {
      $prefix = 'lc-show-';
      /* Remove the lc fields from custom fields in case of using
       * classic editor. We only want the fields to be available
       * in one place for the sake of usability
       */
      if (strpos($meta_key, $prefix) !== false) {
        /* I am not sure what "protected" means, and it is not really
         * protected. This is just a neat trick for hiding a field.
         *
         * For details: https://pixert.com/blog/wordpress-how-to-hide-custom-fields-meta-key-from-custom-fields-panel/
         */
        return true;
      } else {
        return $protected;
      }
    }

    // * Used for the show template
    public function add_metaboxes($id) {
      global $fonts_array;
      global $LC_METABOXES;
      global $is_edit_show_page;
      $LC_METABOXES = array();
      $is_edit_show_page = isset($_GET['post'])&&get_page_template_slug( $_GET['post'])==='show-template.php';
      
      include_once(plugin_dir_path( __FILE__ )  . 'partials/lilicast-google-fonts.php');
      
      $prefix = 'lc-show-';

      // TODO Refactor: extract this hack into its own file.
      function update_metabox_fields() {
        /* How the Meta Box is implemented here is a hack and not a proper way
         * of using the plugin. There is two reasons for this solutions:
         *
         * 1. There is async API call needed for getting some of the consumed
         *    resources
         *
         * 2. WP Bakery or any other plugin using classic editor seems to cause
         *    issues with Meta Box (the fields are not saving when using Visual
         *    Composer). The custom save function (defined later) needs the data
         *    from the metaboxes. Because of the asynchronity (or some other reason)
         *    the fields are not available for normal methods of Meta Box.
         *
         * TODOS:
         *
         * 1. Check regularly the updates of both, Meta Box and WP Bakery Visual
         *    Composer if the issue remains. If the problems are fixed or the
         *    classic editor becomes completely absent, init the metabox according
         *    to docs.
         */
        global $fonts_array;
        global $LC_METABOXES;
        global $is_edit_show_page;
        $LC_METABOXES = array();
        $prefix = 'lc-show-';
        
        /* Define the "backbone" of metaboxes. These will be manipulated later
         * if necessary.
         */

        $show_header_text_domain = $prefix . 'information-meta-box';

        $show_header_metabox_fields = array(
          array(
            'id' => $prefix . 'header_visible',
            'name' => esc_html__( 'Header visible', $show_header_text_domain ),
            'type' => 'checkbox',
            'std' => 'true'
          ),
          array(
            'id' => $prefix . 'logo',
            'type' => 'image_advanced',
            'name' => esc_html__( 'Logo', $show_header_text_domain ),
            'desc' => esc_html__( 'Logo of your show', $show_header_text_domain ),
          ),
          array(
            'id' => $prefix . 'logo_bg_color',
            'name' => esc_html__( 'Logo background color', $show_header_text_domain ),
            'type' => 'color',
            'desc' => esc_html__( 'Set background color for the logo', $show_header_text_domain ),
          ),
          array(
            'id' => $prefix . 'header_font',
            'name' => esc_html__( 'Header font', $show_header_text_domain ),
            'type' => 'select',
            'placeholder' => esc_html__( 'Select font', $show_header_text_domain ),
            'options' => $fonts_array, // coming from previously included file
          ),
          array(
            'id' => $prefix . 'name',
            'type' => 'text',
            'name' => esc_html__( 'Name', $show_header_text_domain ),
            'desc' => esc_html__( 'Enter the name of your show', $show_header_text_domain ),
            'std' => 'ie. Breakfast Ballers',
          ),
          array(
            'id' => $prefix . 'tagline',
            'type' => 'text',
            'name' => esc_html__( 'Tagline', $show_header_text_domain ),
            'desc' => esc_html__( 'Enter the tagline of your show. This comes under the name of your show', $show_header_text_domain ),
            'std' => 'ie. "Wake up entertained!"',
          ),
          array(
            'id' => $prefix . 'header_color',
            'name' => esc_html__( 'Header color', $show_header_text_domain ),
            'type' => 'color',
            'desc' => esc_html__( 'The color of the text shown on top of the page', $show_header_text_domain ),
          ),
          array(
            'id' => $prefix . 'header_bg_color',
            'name' => esc_html__( 'Header background color', $show_header_text_domain ),
            'type' => 'color',
            'desc' => esc_html__( 'The color of the header text background', $show_header_text_domain ),
          ),
          array(
            'id' => $prefix . 'team_alignment',
            'name' => esc_html__( 'Team alignment', $show_header_text_domain ),
            'type' => 'radio',
            'options' => array(
              'right' => 'Right column',
              'center' => 'Center top',
              'hide' => 'None (hide the team)'
            )
          )
        );

        $id = isset($GET['post']) ? $GET['post'] : -1;
        $grid_metabox_text_domain = 'lc-show-video-grid-meta-box';
        $header_color = get_post_meta($id, $prefix . 'header_color');
        $video_control_color = get_post_meta($id, $prefix . 'video_control_color');

        $show_grid_metabox_fields = array(
          array(
            'id' => $prefix . 'show',
            'name' => esc_html__( 'Show', $grid_metabox_text_domain ),
            'type' => 'select',
            'placeholder' => esc_html__( 'Select show', $grid_metabox_text_domain ),
            'options' => array(),
          ),
          array(
            'id' => $prefix . 'tags',
            'name' => 'Filter by tags',
            'type' => 'fieldset_text',
            'options' => array(
              'tag' => 'Tag:'
            ),
            'clone' => true

          ),
          array(
            'id' => $prefix . 'scroll_to_video',
            'name' => esc_html__( 'Scroll to video on open', $grid_metabox_text_domain ),
            'type' => 'checkbox',
            'std' => 'true'
          ),
          array(
            'id' => $prefix . 'video_cover_bg_color',
            'name' => esc_html__( 'Cover image overlay background color', $grid_metabox_text_domain ),
            'type' => 'color',
            'desc' => esc_html__( 'The color of video cover image background, when hovering with the mouse', $grid_metabox_text_domain ),
            'std' => isset($header_color[0]) ? $header_color[0] : 'orange'
          ),
          array(
            'id' => $prefix . 'video_cover_primary_color',
            'name' => esc_html__( 'Video cover overlay text color', $grid_metabox_text_domain ),
            'type' => 'color',
            'desc' => esc_html__( 'Color of the video cover overlay text', $grid_metabox_text_domain ),
          ),
          array(
            'id' => $prefix . 'video_article_bg_color',
            'name' => esc_html__( 'Article background color', $grid_metabox_text_domain),
            'type' => 'color',
            'std' => '#212025'
          ),
          array(
            'id' => $prefix . 'video_article_title_color',
            'name' => esc_html__( 'Title color', $grid_metabox_text_domain ),
            'type' => 'color',
            'std' => isset($header_color[0]) ? $header_color[0] : 'white'
          ),
          array(
            'id' => $prefix . 'video_article_text_color',
            'name' => esc_html__( 'Text color', $grid_metabox_text_domain),
            'type' => 'color',
            'std' => 'whitesmoke'
          ),

          array(
            'id' => $prefix . 'video_article_highlight_color',
            'name' => esc_html__( 'Highlight color', $grid_metabox_text_domain),
            'type' => 'color',
          ),

          array(
            'id' => $prefix . 'video_control_color',
            'name' => esc_html__( 'Video control color', $grid_metabox_text_domain ),
            'type' => 'color',
            'std' =>  'whitesmoke'
          ),
          array(
            'id' => $prefix . 'video_control_hover_color',
            'name' => esc_html__( 'Video control hover color', $grid_metabox_text_domain ),
            'desc' => esc_html( 'What color to show on mouseover of video controls' ),
            'type' => 'color',
            'std' => isset($header_color[0]) ? $header_color[0] : '#1e73be'
          ),
          array(
            'id' => $prefix . 'video_progress_bar_color',
            'name' => esc_html__( 'Video progress bar color', $grid_metabox_text_domain ),
            'type' => 'color',
            'std' => isset($header_color[0]) ? $header_color[0] : '#1e73be'
          ),
          array(
            'id' => $prefix . 'video_progress_bar_bg_color',
            'name' => esc_html__( 'Video progress bar background color', $grid_metabox_text_domain ),
            'type' => 'color',
            'std' => isset($video_control_color[0]) ? $video_control_color[0] : 'whitesmoke'
          ),
        );

        /* Metabox data manipulation functions start
         */

        $api_key = get_option('lilicast_api_key');
        $show_array = array();

        $shows_index =  array_search($prefix . 'show', array_column($show_grid_metabox_fields, 'id'));
        $shows_set = false;

        if ($api_key) {
          $api_response = LilicastApiWrapper::get_shows($api_key);

          if (isset($api_response['error_message'])) {
            $no_shows_error_field = array(
              'type' => 'custom_html',
              'std'  => '<div class="rwmb-label"><label>Select show</label></div><div class="rwmb-input"><span class="field-notice alert">Can not get the shows from LiLiCAST app. Make sure you have a right API key in the plugin settings.</span></div>'
            );
            array_splice($show_grid_metabox_fields, 0, 1, array($no_shows_error_field));
          } else {          
            $shows = $api_response['result'];
            
            foreach ($shows as $key => $show) {
              $show_array[$show->id] = $show->name;
            }
            
            $show_field = array(
              array(
                'id' => $prefix . 'show',
                'name' => esc_html__( 'Show', $show_header_text_domain ),
                'type' => 'select',
                'placeholder' => esc_html__( 'Select show', $show_header_text_domain ),
                'options' => $show_array,
              )
            );
            array_splice($show_grid_metabox_fields, 0, 1, $show_field);
          }
        }

        $lc_show_header_meta_boxes = array(
          'id' => $prefix . 'header_metabox',
          'title' => esc_html__( 'Show header', 'lc-show-header-meta-box' ),
          'post_types' => array('page' ),
          'context' => 'advanced',
          'priority' => 'high',
          'autosave' => 'false',
          'fields' => $show_header_metabox_fields
        );

        $lc_show_grid_meta_boxes= array(
          'id' => $prefix . 'video_grid_metabox',
          'title' => esc_html__( 'Video grid settings', $grid_metabox_text_domain),
          'post_types' => array('page'),
          'context' => 'advanced',
          'priority' => 'high',
          'autosave' => 'false',
          'fields' => $show_grid_metabox_fields
        );

        array_push($LC_METABOXES, $lc_show_header_meta_boxes);
        array_push($LC_METABOXES, $lc_show_grid_meta_boxes);

        return $LC_METABOXES;
      }

      /* $LC_METABOXES has to be set before running the classic editor save function.
       * Not sure why, since it seems unnecessary.
       */

      if (!$this->is_gutenberg) {
        update_metabox_fields();
      }

      if ($is_edit_show_page) {
        function lc_show_meta_boxes( $meta_boxes ) {
          $metaboxes_to_set = update_metabox_fields();
          foreach ($metaboxes_to_set as $key => $meta_box) {
            $meta_boxes[] = $meta_box;
          }
          return $meta_boxes;
        }

        add_filter( 'rwmb_meta_boxes', 'lc_show_meta_boxes', 20, 5 );
      }

    }

    public function classic_editor_save_metaboxes($id) {
      /* The original reason for this function:
       * https://github.com/wpmetabox/meta-box/issues/1333
       */
      global $LC_METABOXES;
      $flat_fields = array();

      if ($this->is_gutenberg) {
        return;
      }

      if (get_page_template_slug( $id )!=='show-template.php') {
        return;
      }

      foreach ($LC_METABOXES as $key => $mb) {
        $flat_fields = array_merge($flat_fields, $mb['fields']);
      };

      if ($id) {
        foreach ($flat_fields as $key => $f) {
          if (isset($f['id'])) {
            $fid = sanitize_text_field($f['id']);
            /* At the time of initial building, has not been tested against all
             * of the Meta Box types.
             *
             * Bear this in mind when extending the fields
             */
            if (!isset($_POST[$fid])) {
              if ($f['type']==='checkbox') {
                update_post_meta($id, $fid, 0);
              }
            } else if (gettype($_POST[$fid])==='array'&&$f['type']!=='fieldset_text') {
              delete_post_meta($id, $fid);
              foreach ($_POST[$fid] as $key => $store_this) {
                add_post_meta($id, $fid, sanitize_text_field($store_this));
              }            
            } else {
              update_post_meta($id, $fid, sanitize_text_field($_POST[$fid]));
            }
          }
        }
      }
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {

        /** 
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Lilicast_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Lilicast_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/lilicast-admin.css', array(), $this->version, 'all' );

    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Lilicast_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Lilicast_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/lilicast-admin.js', array( 'jquery' ), $this->version, false );

    }

}
