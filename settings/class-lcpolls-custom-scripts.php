<?php
/**
 * LcPolls_Custom_Scripts
 *
 * Load assets
 *
 * @version 1.0
 * @author Nikola Nikoloski
 */
class LcPolls_Custom_Scripts
{
    private static $instance = null;

    /**
     * Returns a single instance of this class.
     */
    public static function getInstance ()
    {

        if ( null == self::$instance ) {
            self::$instance = new LcPolls_Custom_Scripts();
        }

        return self::$instance;
    }

    /**
     * class constructor
     */
    public function __construct ()
    {
        add_action( 'admin_enqueue_scripts', [ $this, 'lc_polls_enqueue_custom_admin_scripts' ] );
        add_action( 'wp_enqueue_scripts', [$this, 'lc_polls_enqueue_custom_public_scripts'], 100, 1);
    }

    /**
     * Register and enqueue a custom scripts in the WordPress admin.
     */
    public function lc_polls_enqueue_custom_admin_scripts () {
        if(is_admin()){
            //Admin Styles
            wp_register_script( 'jq-ui-script', 'https://code.jquery.com/ui/1.12.1/jquery-ui.js', array ( 'jquery' ), 1.1, false);
            wp_register_style('jq-ui-style', 'https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css');

            wp_enqueue_script('jq-ui-script');
            wp_enqueue_style('jq-ui-style');

        }
    }

    public function lc_polls_enqueue_custom_public_scripts()
    {
        //Check and set ENV variable
        $env = defined('LCPOLLS_ENV') && LCPOLLS_ENV !== ''  ? LCPOLLS_ENV : 'production';

        //Setting the assets urls
        if($env === 'develop') {
            $jsUrl = LCPOLLS_PLUGIN_URL.'build/dev/js/bundle.js';
            $cssUrl = LCPOLLS_PLUGIN_URL . 'build/dev/css/lcpolls-main.css';
        } else {
            $string = file_get_contents(LCPOLLS_PLUGIN_URL.'build/public/rev-manifest.json');
            $manifest = json_decode($string, true);
            $jsUrl = LCPOLLS_PLUGIN_URL.'build/public/js/'.$manifest['bundle.min.js'];
            $cssUrl = LCPOLLS_PLUGIN_URL . 'build/public/css/'.$manifest['lcpolls-main.min.css'];
        }

        //registering and enqueuing assets on frontend
        wp_register_style('lcpolls-main', $cssUrl, [], '1.0.0');
        wp_register_script( 'lcpolls-bundle',  $jsUrl, [], '1.0.0', true );

        wp_enqueue_style('lcpolls-main');
        wp_enqueue_script('lcpolls-bundle');
    }

}