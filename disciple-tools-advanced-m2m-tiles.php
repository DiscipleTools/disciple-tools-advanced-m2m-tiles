<?php
/**
 *Plugin Name: Disciple.Tools - Advanced M2M Tiles
 * Plugin URI: https://github.com/DiscipleTools/disciple-tools-advanced-m2m-tiles
 * Description: Disciple.Tools - Advanced M2M Tiles adds specific supporting tiles for different roles.
 * Version:  2.2.1
 * Author URI: https://github.com/DiscipleTools
 * GitHub Plugin URI: https://github.com/DiscipleTools/disciple-tools-advanced-m2m-tiles
 * Requires at least: 4.7.0
 * (Requires 4.7+ because of the integration of the REST API at 4.7 and the security requirements of this milestone version.)
 * Tested up to: 5.6
 *
 * @package disciple-tools-advanced-m2m-tiles
 * @link    https://github.com/DiscipleTools
 * @license GPL-2.0 or later
 *          https://www.gnu.org/licenses/gpl-2.0.html
 */


if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
$dt_roles_required_dt_theme_version = '1.0';

/**
 * Gets the instance of the `dt_roles_tiles` class.
 *
 * @since  0.1
 * @access public
 * @return object|bool
 */
add_action( 'after_setup_theme', function() {
    global $dt_roles_required_dt_theme_version;
    $wp_theme = wp_get_theme();
    $version = $wp_theme->version;
    /*
     * Check if the Disciple.Tools theme is loaded and is the latest required version
     */
    $is_theme_dt = class_exists( "Disciple_Tools" );
    if ( $is_theme_dt && version_compare( $version, $dt_roles_required_dt_theme_version, "<" ) ) {
        add_action( 'admin_notices', 'dt_roles_tiles_hook_admin_notice' );
        add_action( 'wp_ajax_dismissed_notice_handler', 'dt_hook_ajax_notice_handler' );
        return false;
    }
    if ( !$is_theme_dt ){
        return false;
    }
    /**
     * Load useful function from the theme
     */
    if ( !defined( 'DT_FUNCTIONS_READY' ) ){
        require_once get_template_directory() . '/dt-core/global-functions.php';
    }
    /*
     * Don't load the plugin on every rest request. Only those with the 'sample' namespace
     */
    require_once( 'includes/dt-hooks.php' ); //allways load
    $is_rest = dt_is_rest();
    if ( !$is_rest || strpos( dt_get_url_path(), 'dt-roles' ) !== false ){
        return DT_Advanced_M2M_Tiles::get_instance();
    }
    return false;
}, 50 );

/**
 * Singleton class for setting up the plugin.
 *
 * @since  0.1
 * @access public
 */
class DT_Advanced_M2M_Tiles {

    /**
     * Declares public variables
     *
     * @since  0.1
     * @access public
     * @return object
     */
    public $token;
    public $version;
    public $dir_path = '';
    public $dir_uri = '';
    public $img_uri = '';
    public $includes_path;

    /**
     * Returns the instance.
     *
     * @since  0.1
     * @access public
     * @return object
     */
    public static function get_instance() {

        static $instance = null;

        if ( is_null( $instance ) ) {
            $instance = new DT_Advanced_M2M_Tiles();
            $instance->setup();
            $instance->includes();
            $instance->setup_actions();
        }
        return $instance;
    }

    /**
     * Constructor method.
     *
     * @since  0.1
     * @access private
     * @return void
     */
    private function __construct() {
    }

    /**
     * Loads files needed by the plugin.
     *
     * @since  0.1
     * @access public
     * @return void
     */
    private function includes() {
        if ( is_admin() ) {
            require_once( 'includes/admin/admin-menu-and-tabs.php' );
        }

        require_once( "includes/tiles.php" );

        require_once( 'includes/rest-api.php' );
        DT_Advanced_M2M_Tiles_Endpoints::instance();
    }

    /**
     * Sets up globals.
     *
     * @since  0.1
     * @access public
     * @return void
     */
    private function setup() {

        // Main plugin directory path and URI.
        $this->dir_path     = trailingslashit( plugin_dir_path( __FILE__ ) );
        $this->dir_uri      = trailingslashit( plugin_dir_url( __FILE__ ) );

        // Plugin directory paths.
        $this->includes_path      = trailingslashit( $this->dir_path . 'includes' );

        // Plugin directory URIs.
        $this->img_uri      = trailingslashit( $this->dir_uri . 'img' );

        // Admin and settings variables
        $this->token             = 'disciple-tools-advanced-m2m-tiles';
        $this->version             = '1.1';

    }

    /**
     * Sets up main plugin actions and filters.
     *
     * @since  0.1
     * @access public
     * @return void
     */
    private function setup_actions() {
        // Internationalize the text strings used.
        add_action( 'after_setup_theme', array( $this, 'i18n' ), 51 );
    }

    /**
     * Method that runs only when the plugin is activated.
     *
     * @since  0.1
     * @access public
     * @return void
     */
    public static function activation() {

        // Confirm 'Administrator' has 'manage_dt' privilege. This is key in 'remote' configuration when
        // Disciple.Tools theme is not installed, otherwise this will already have been installed by the Disciple.Tools Theme
        $role = get_role( 'administrator' );
        if ( !empty( $role ) ) {
            $role->add_cap( 'manage_dt' ); // gives access to dt plugin options
        }

    }

    /**
     * Method that runs only when the plugin is deactivated.
     *
     * @since  0.1
     * @access public
     * @return void
     */
    public static function deactivation() {
        delete_option( 'dismissed-dt-roles' );
    }

    /**
     * Loads the translation files.
     *
     * @since  0.1
     * @access public
     * @return void
     */
    public function i18n() {
        $domain = 'disciple-tools-advanced-m2m-tiles';
        $locale = apply_filters(
            'plugin_locale',
            ( is_admin() && function_exists( 'get_user_locale' ) ) ? get_user_locale() : get_locale(),
            $domain
        );

        $mo_file = $domain . '-' . $locale . '.mo';
        $path = realpath( dirname( __FILE__ ) . '/languages' );

        if ($path && file_exists( $path )) {
            load_textdomain( $domain, $path . '/' . $mo_file );
        }
    }

    /**
     * Magic method to output a string if trying to use the object as a string.
     *
     * @since  0.1
     * @access public
     * @return string
     */
    public function __toString() {
        return 'disciple-tools-advanced-m2m-tiles';
    }

    /**
     * Magic method to keep the object from being cloned.
     *
     * @since  0.1
     * @access public
     * @return void
     */
    public function __clone() {
        _doing_it_wrong( __FUNCTION__, esc_html( 'Whoah, partner!' ), '0.1' );
    }

    /**
     * Magic method to keep the object from being unserialized.
     *
     * @since  0.1
     * @access public
     * @return void
     */
    public function __wakeup() {
        _doing_it_wrong( __FUNCTION__, 'Whoah, partner!', '0.1' );
    }

    /**
     * Magic method to prevent a fatal error when calling a method that doesn't exist.
     *
     * @since  0.1
     * @access public
     * @return null
     */
    public function __call( $method = '', $args = array() ) {
        _doing_it_wrong( "DT_Advanced_M2M_Tiles::" . esc_html( $method ), 'Method does not exist.', '0.1' );
        unset( $method, $args );
        return null;
    }
}
// end main plugin class


function dt_roles_tiles_hook_admin_notice() {
    global $dt_roles_required_dt_theme_version;
    $wp_theme = wp_get_theme();
    $current_version = $wp_theme->version;
    $message = "'Disciple.Tools - Advanced M2M Tiles' plugin requires 'Disciple.Tools' theme to work. Please activate 'Disciple.Tools' theme or make sure it is latest version.";
    if ( $wp_theme->get_template() === "disciple-tools-theme" ){
        $message .= sprintf( esc_html( 'Current Disciple.Tools version: %1$s, required version: %2$s' ), esc_html( $current_version ), esc_html( $dt_roles_required_dt_theme_version ) );
    }
    // Check if it's been dismissed...
    if ( ! get_option( 'dismissed-dt-roles', false ) ) { ?>
        <div class="notice notice-error notice-dt-roles is-dismissible" data-notice="dt-roles">
            <p><?php echo esc_html( $message );?></p>
        </div>
        <script>
            jQuery(function($) {
                $( document ).on( 'click', '.notice-dt-roles .notice-dismiss', function () {
                    $.ajax( ajaxurl, {
                        type: 'POST',
                        data: {
                            action: 'dismissed_notice_handler',
                            type: 'dt-roles',
                            security: '<?php echo esc_html( wp_create_nonce( 'wp_rest_dismiss' ) ) ?>'
                        }
                    })
                });
            });
        </script>
    <?php }
}


/**
 * AJAX handler to store the state of dismissible notices.
 */
if ( !function_exists( "dt_hook_ajax_notice_handler" )){
    function dt_hook_ajax_notice_handler(){
        check_ajax_referer( 'wp_rest_dismiss', 'security' );
        if ( isset( $_POST["type"] ) ){
            $type = sanitize_text_field( wp_unslash( $_POST["type"] ) );
            update_option( 'dismissed-' . $type, true );
        }
    }
}


/**
 * Check for plugin updates even when the active theme is not Disciple.Tools
 *
 * Below is the publicly hosted .json file that carries the version information. This file can be hosted
 * anywhere as long as it is publicly accessible. You can download the version file listed below and use it as
 * a template.
 * Also, see the instructions for version updating to understand the steps involved.
 * @see https://github.com/DiscipleTools/disciple-tools-version-control/wiki/How-to-Update-the-Starter-Plugin
 */
add_action( 'plugins_loaded', function (){
    if ( is_admin() && ! is_multisite() || is_network_admin() || wp_doing_cron() ){
        if ( ! class_exists( 'Puc_v4_Factory' ) ) {
            // find the Disciple.Tools theme and load the plugin update checker.
            foreach ( wp_get_themes() as $theme ){
                if ( $theme->get( 'TextDomain' ) === "disciple_tools" && file_exists( $theme->get_stylesheet_directory() . '/dt-core/libraries/plugin-update-checker/plugin-update-checker.php' ) ){
                    require( $theme->get_stylesheet_directory() . '/dt-core/libraries/plugin-update-checker/plugin-update-checker.php' );
                }
            }
        }
        if ( class_exists( 'Puc_v4_Factory' ) ){
            $hosted_json = "https://raw.githubusercontent.com/DiscipleTools/disciple-tools-advanced-m2m-tiles/master/version-control.json";
            Puc_v4_Factory::buildUpdateChecker(
                $hosted_json,
                __FILE__,
                'disciple-tools-advanced-m2m-tiles'
            );

        }
    }
} );
