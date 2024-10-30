<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       app.lilicast.com
 * @since      1.0.0
 *
 * @package    Lilicast
 * @subpackage Lilicast/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Lilicast
 * @subpackage Lilicast/public
 * @author     Jaakko Karhu <jaakko@26lights.com>
 */

require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/partials/render-helper-functions.php';

class Lilicast_Public {

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
     * @param      string    $plugin_name       The name of the plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct( $plugin_name, $version) {

        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    public function debug_log($msg) {
        error_log("[ " . date('Y-m-d: h:i') .  " ] " . $msg . "\n", 3, plugin_dir_path( dirname( __FILE__ )) . "debug.log" );
    }

    public function disable_wp_auto_p($content) {
      /* Is needed for preventing the addition of
       * unnecessary html when creating the "show all"
       * LiLiCAST grid
       */
      if (isset($_GET['is_lc'])) {
        remove_filter( 'the_content', 'wpautop' );
        remove_filter( 'the_excerpt', 'wpautop' );
      }

      return $content;
    }

    public function render_lilicasts_netflix_style($attrs = array()) {
      $page = (get_query_var( 'paged' )) ? get_query_var( 'paged' ) : 1;

      $filters = LilicastVideoGrid::build_filters($attrs);
      $renderer = new LilicastVideoGridNetflix($attrs, $filters);
      $html = $renderer->render($page);
      return $html;
    }

    public function render_lilicasts($attrs = array()) {
      $page = (get_query_var( 'paged' )) ? get_query_var( 'paged' ) : 1;

      $filters = LilicastVideoGrid::build_filters($attrs);
      $wp_query = LilicastVideoGrid::wp_query($attrs, $filters, $page);

      $hide_show_all = ( isset($atts['hide_show_all']) && $atts['hide_show_all'] );
      
      $html = '';
      if ( $wp_query->have_posts() ) {
        $html .= LilicastVideoGrid::get_video_grid($wp_query, $attrs);
        
        $permalink_structure = get_option( 'permalink_structure' );
        if ($wp_query->max_num_pages != 1 && !!$permalink_structure && !$hide_show_all) {
          $html .= '<a href="' . LilicastVideoGrid::build_url_to_show_all($attrs, $filters) . '">';
          $html .=    '<button type="button" class="lc-sc-show-all-button">Show all</button>';
          $html .= '</a>';
        } elseif ($wp_query->max_num_pages != 1) {
          $html .= LilicastVideoGrid::html_pagination($page, $wp_query->max_num_pages);
        }
      }
      return $html;
    } // render_lilicasts

    // Register the shortcode into Wordpress
    public function register_shortcodes() {
      add_shortcode( 'lilicast_list', array( $this, 'render_lilicasts' ));
      add_shortcode( 'lilicast_flix_list', array( $this, 'render_lilicasts_netflix_style' ));
    } // register_shortcodes()

    // Register the endpoint to sync from Lilicast App.
    public function register_api_endpoints() {
        include_once( ABSPATH . 'wp-admin/includes/image.php' );
        include_once( ABSPATH . 'wp-admin/includes/media.php' );

        $lilicast_test_callback = function(WP_REST_Request $request) {
            return LilicastSyncFromApp::callback($request);
        };

        register_rest_route( 'lilicast/v1', '/upload', array(
            'methods' => 'POST',
            'callback' => $lilicast_test_callback
        ));

        $lilicast_test_callback = function(WP_REST_Request $request) {
            return new WP_REST_Response( array("message" => 'works'), 200);
        };

        register_rest_route( 'lilicast/v1', '/test', array(
            'methods' => 'GET',
            'callback' => $lilicast_test_callback
        ));
    } // register_api_endpoints

    // Disabling the feature as this seems quite incorrect ...
    // Seems to be used when Show All is pressed from the shortcode rendering.
    // It doesn't look like this is used for lilicast show_template...
    // TOTO Refactor: extract args processing into LilicastVideoGrid and reuse code with render_lilicasts. 
    function generate_show_all_page() {
        global $wp_query;
        $get_grid = function() {        

            // build shortcode-like attributes from the URL params
            $attrs = array();

            if (isset($_GET["cats"])) { 
              $attrs['categories'] = sanitize_text_field($_GET["cats"]); 
            }
            if (isset($_GET["tags"])) { 
              $attrs['tags'] = sanitize_text_field($_GET["tags"]);
            }
            if (isset($_GET["page_max"])) { 
              $attrs['page_max'] = sanitize_text_field($_GET["page_max"]); 
            }
            if (isset($_GET["display_title"])) { 
              $attrs['display_title'] = sanitize_text_field($_GET["display_title"]); 
            }
            $page = 1;
            if (isset($_GET['page'])) {
              $page = sanitize_text_field($_GET['page']);
            }

            $filters = LilicastVideoGrid::build_filters($attrs);
            $wp_query = LilicastVideoGrid::wp_query($attrs, $filters, $page);

            if ( $wp_query->have_posts() ) {
              $html = '';
              $html .= LilicastVideoGrid::get_video_grid($wp_query, $attrs);
              $html .= LilicastVideoGrid::html_pagination($page, $wp_query->max_num_pages);
            }
            return $html;
        };
        
        // (?) Creates a fake Post as a null object with categories 
        // (?) to show the grid ... cause the grid needs something ?
        if($wp_query->is_404 ) {
            if (isset($_GET["is_lc"])) {
                $post_categories = array();
                if (isset($_GET["post_category"])) {
                  array_push($post_categories, sanitize_text_field($_GET["post_category"]));
                } else {
                  array_push($post_categories, 'uncategorized');
                }
                
                $post = new stdClass();
                $post->ID= -999999; // need an id
                $post->post_category = $post_categories; //Add some categories. an array()???
                $post->post_content=$get_grid(); //The full text of the post.
                //$post->post_excerpt= 'hey here we are a real post'; //For all your post excerpt needs.
                $post->post_status='publish'; //Set the status of the new post.
                $post->post_title=null; //The title of your post.
                $post->post_date=null;
                $post->post_name= 'LiLiCAST list';
                $post->post_type='post'; //Sometimes you might want to post a page.
                $post->comment_status='closed';

                // (?)fake pagination
                $wp_query->queried_object=$post;
                $wp_query->post=$post;
                $wp_query->found_posts = 1;
                $wp_query->post_count = 1;
                $wp_query->max_num_pages = 1;
                $wp_query->is_single = 1;
                $wp_query->is_404 = false;
                $wp_query->is_posts_page = 1;
                $wp_query->posts = array($post);
                $wp_query->page=false;
                $wp_query->is_post=true;
                $wp_query->page=false;
            }
        }
    } // generate_show_all_page

    public function remove_filters() {
        /**
         * Some themes add filters, which interfere with lilicast grid
         * rendering by echoing the result instead of returning.
         * Removing all filters prevents that.
         */
        remove_all_filters('wp_video_shortcode');
    } // remove_filters

    // Add HTML SEO/META TAGS  in case the post is a lilicast
    public function add_og() {
        $meta = get_post_meta(get_the_ID());
        if (is_array($meta)&&array_key_exists('lilicast_article_for', $meta)) {
            echo '<meta property="og:title" content="' . get_the_title() . '">';
            $args = array(
                'post_type'      => array('attachment'),
                'numberposts'    => 1,
                'post_status'    => "any",
                'meta_query' => array(
                    array(
                        'key'     => 'lilicast_cover_for',
                        'value'   => $meta['lilicast_article_for'][0],
                        'compare' => '='
                    )
                )
            );
            $query = new WP_Query($args);
            if ($query->have_posts()) {
                while ( $query->have_posts()) : $query->the_post();
                    $img_url = wp_get_attachment_url(get_the_ID());
                    echo '<meta property="og:image" content="' . $img_url . '" />';
                endwhile;
            }
        };
    } // add_og

    /**
     * Register the stylesheets for the public-facing side of the site.
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

        wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/lilicast-public.css', array(), $this->version, 'all' );
    } // enqueue_styles

    /**
     * Register the JavaScript for the public-facing side of the site.
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

        wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/lilicast-public.js', array( 'jquery' ), $this->version, false );
    } // enqueue_scripts

}
