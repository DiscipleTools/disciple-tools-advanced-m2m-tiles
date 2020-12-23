<?php
/**
 * DT_Roles_Plugin_Menu class for the admin page
 *
 * @class       DT_Roles_Plugin_Menu
 * @version     0.1.0
 * @since       0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; // Exit if accessed directly
}

/**
 * Initialize menu class
 */
DT_Roles_Plugin_Menu::instance();

/**
 * Class DT_Roles_Plugin_Menu
 */
class DT_Roles_Plugin_Menu {

    public $token = 'dt_roles_tiles';
    public $fields = [ "assigned_to", "my_actions" ];

    private static $_instance = null;

    /**
     * DT_Roles_Plugin_Menu Instance
     *
     * Ensures only one instance of DT_Roles_Plugin_Menu is loaded or can be loaded.
     *
     * @since 0.1.0
     * @static
     * @return DT_Roles_Plugin_Menu instance
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    } // End instance()


    /**
     * Constructor function.
     * @access  public
     * @since   0.1.0
     */
    public function __construct() {

        add_action( "admin_menu", array( $this, "register_menu" ) );

    } // End __construct()


    /**
     * Loads the subnav page
     * @since 0.1
     */
    public function register_menu() {
        add_menu_page( __( 'Extensions (DT)', 'dt_roles_tiles' ), __( 'Extensions (DT)', 'dt_roles_tiles' ), 'manage_dt', 'dt_extensions', [ $this, 'extensions_menu' ], 'dashicons-admin-generic', 59 );
        add_submenu_page( 'dt_extensions', __( 'Roles Plugin', 'dt_roles_tiles' ), __( 'Roles Plugin', 'dt_roles_tiles' ), 'manage_dt', $this->token, [ $this, 'content' ] );
    }

    /**
     * Menu stub. Replaced when Disciple Tools Theme fully loads.
     */
    public function extensions_menu() {
    }

    /**
     * Builds page contents
     * @since 0.1
     */
    public function content() {

        if ( !current_user_can( 'manage_dt' ) ) { // manage dt is a permission that is specific to Disciple Tools and allows admins, strategists and dispatchers into the wp-admin
            wp_die( esc_attr__( 'You do not have sufficient permissions to access this page.' ) );
        }


        $this->save_settings();

        $this->display_content();

    }

    public function save_settings(){
        if ( !isset( $_POST["submit_roles_save"] ) ) {
            return;
        }
        if ( isset( $_POST["dt_roles_nonce"] ) && !wp_verify_nonce( wp_unslash( sanitize_key( $_POST["dt_roles_nonce"] ) ), "save" ) ) {
            exit;
        }
        $roles_settings = get_option( "dt_roles_settings", [] );
        foreach ( $this->fields as $field ){
            if ( !isset( $roles_settings[$field] ) ) {
                $roles_settings[$field] = [ "enabled" => true ];
            }
            $roles_settings[$field]["enabled"] = isset( $_POST["{$field}_enabled"] );
        }

        update_option( "dt_roles_settings", $roles_settings );
    }


    public function display_content() {
        ?>
        <h2>Roles Tiles Settings</h2>
        <div class="wrap">
            <div id="poststuff">
                <div id="post-body" class="metabox-holder columns-2">
                    <div id="post-body-content">
                        <!-- Main Column -->

                        <?php $this->main_column() ?>

                        <!-- End Main Column -->
                    </div><!-- end post-body-content -->
                    <div id="postbox-container-1" class="postbox-container">
                        <!-- Right Column -->

                        <?php // $this->right_column() ?>

                        <!-- End Right Column -->
                    </div><!-- postbox-container 1 -->
                    <div id="postbox-container-2" class="postbox-container">
                    </div><!-- postbox-container 2 -->
                </div><!-- post-body meta box container -->
            </div><!--poststuff end -->
        </div><!-- wrap end -->
        <?php
    }

    public function main_column() {
        $roles_settings = get_option( "dt_roles_settings", [] );
        $assigned_to_enabled = isset( $roles_settings["assigned_to"]["enabled"] ) ? $roles_settings["assigned_to"]["enabled"] : false;
        $my_actions_enabled = isset( $roles_settings["my_actions"]["enabled"] ) ? $roles_settings["my_actions"]["enabled"] : false;
        ?>
        <form action="" method="post">
            <?php wp_nonce_field( 'save', 'dt_roles_nonce' ) ?>
            <table class="widefat striped">
                <thead>
                <tr>
                    <th>Tiles</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>
                        <label>
                            Dispatch For Tile
                            <input type="checkbox" name="assigned_to_enabled" <?php checked( $assigned_to_enabled ) ?>>
                        </label>
                    </td>
                </tr>
                <tr>
                    <td>
                        <label>
                            My actions Tile
                            <input type="checkbox" name="my_actions_enabled" <?php checked( $my_actions_enabled ) ?>>
                        </label>

                    </td>
                </tr>
                <tr>
                    <td>
                        <input type="submit" name="submit_roles_save" value="Save">

                    </td>
                </tr>
                </tbody>
            </table>
        </form>
        <br>
        <?php
    }

    public function right_column() {
        ?>
        <!-- Box -->
        <table class="widefat striped">
            <thead>
            <th>Information</th>
            </thead>
            <tbody>
            <tr>
                <td>
                    Content
                </td>
            </tr>
            </tbody>
        </table>
        <br>
        <!-- End Box -->
        <?php
    }
}

/**
 * Class DT_Roles_Tab_General
 */
class DT_Roles_Tab_General
{


}
