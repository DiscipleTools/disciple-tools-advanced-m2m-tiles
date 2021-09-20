<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

class DT_Advanced_M2M_Tiles_Endpoints {
    public $permissions = [ 'dt_all_access_contacts' ];

    private static $_instance = null;
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    } // End instance()

    public function __construct() {
        add_action( 'rest_api_init', [ $this, 'add_api_routes' ] );
    }

    public function has_permission(){
        $pass = false;
        foreach ( $this->permissions as $permission ){
            if ( current_user_can( $permission ) ){
                $pass = true;
            }
        }
        return $pass;
    }


    public function add_api_routes() {
        $namespace = 'dt-roles/v1';

    }


}
