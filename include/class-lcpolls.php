<?php
/**
 * LcPolls
 *
 * Main plugin class for registering/unregistering plugin functionality
 *
 * @version 1.0
 * @author Nikola Nikoloski
 */

class LcPolls {
    private static $instance = null;

    /**
     * Returns a single instance of this class.
     */
    public static function getInstance ()
    {

        if ( null == self::$instance ) {
            self::$instance = new LcPolls();
        }

        return self::$instance;
    }

    /**
     * class constructor
     */
    public function __construct ()
    {
        // autoload classes
        spl_autoload_register ( [ $this, 'autoload' ] );

        // plugin initialization
        add_action ( 'plugins_loaded', [ $this, 'init' ], 1 );
    }

    /**
     * Autoload plugin classes
     */
    public static function autoload ( $class )
    {
        // exit, if not a trainee class
        if ( 0 !== strncmp ( 'LcPolls_', $class, 8 ) ) {
            return;
        }

        $class = 'class-' . str_replace ( '_', '-', strtolower ( $class ) );

        //locations of all trainee plugin class files
        $dirs = array(
            LCPOLLS_PLUGIN_DIR . "include/api",
            LCPOLLS_PLUGIN_DIR . "include/cpt",
            LCPOLLS_PLUGIN_DIR . "settings",
            LCPOLLS_PLUGIN_DIR . "include/cpt/meta-boxes",
            LCPOLLS_PLUGIN_DIR . "include/shortcodes",
        );

        //autoload requested class
        foreach ( $dirs as $dir ) {
            if ( is_file ( $file = "$dir/$class.php" ) ) {
                require_once ( $file );
                return;
            }
        }
    }

    /**
     * Return post meta
     * @param string $value
     * @param integer $post_id
     * @return String
     */
    public static function lcpolls_get_post_meta( $value, $post_id=false )
    {
        global $post;
        $id = !$post_id ? $post->ID : $post_id;
        $field = get_post_meta( $id, $value, true );
        if ( ! empty( $field ) ) {
            return is_array( $field ) ? stripslashes_deep( $field ) : stripslashes( wp_kses_decode_entities( $field ) );
        } else {
            return false;
        }
    }

    /**
     * Return user meta
     * @param string $value
     * @param integer $user_id
     * @return String
     */
    public static function  lcpolls_get_user_meta( $value, $user_id )
    {

        $field = get_user_meta( $user_id, $value, true );
        if ( ! empty( $field ) ) {
            return is_array( $field ) ? stripslashes_deep( $field ) : stripslashes( wp_kses_decode_entities( $field ) );
        } else {
            return false;
        }
    }

    /**
     * Return user meta
     * @param string $name
     * @param Object $data
     * @return String
     */
    public static function view( $name, $data=false )
    {
        $viewData = $data;
        ob_start();
        include LCPOLLS_PLUGIN_DIR . '/views/public/' . $name . '.php';

        return ob_get_clean();
    }

    public static function footerContent()
    {
        ?>
        <div class="poll_statistics_popup_shadow">
        </div>
        <div id="poll_statistics_popup">
            <span class="close-statistics-popup"><img src="<?php echo LCPOLLS_PLUGIN_URL; ?>/assets/images/error-circle.svg" alt=""></span>
            <div class="popup-content">

            </div>
        </div>
        <?php
    }

    /**
     * Initializes WordPress hooks
     */
    public function init ()
    {
        LcPolls_Api_Routes::getInstance();
        LcPolls_Api_Controller::getInstance();
        LcPolls_Custom_Scripts::getInstance();
        LcPolls_Cpt::getInstance();
        LcPolls_Metafield_Generator::getInstance();
        LcPolls_Metaboxes::getInstance();
        LcPolls_Shortcodes::getInstance();
        add_action('wp_head', [new LcPolls_Global_Js_Variables(), 'setJsVars']);
        add_action('wp_footer', [$this, 'footerContent']);
    }
}