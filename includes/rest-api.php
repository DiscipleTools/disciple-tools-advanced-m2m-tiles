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

        register_rest_route(
            $namespace, '/dispatch-lists', [
                'methods' => 'GET',
                'callback' => [ $this, 'get_dispatch_list' ],
                'permission_callback' => '__return_true',
            ]
        );
    }


    public function get_dispatch_list( WP_REST_Request $request ) {
        if ( !$this->has_permission() ) {
            return new WP_Error( __FUNCTION__, __( "No permission" ), [ 'status' => 403 ] );
        }
        global $wpdb;
        $params = $request->get_query_params();

        $user_data = DT_User_Management::get_users( false );
//        @todo get users that are in contact's loctanios
//        get multipliers that are of the same gender- meh
//        get multipliers that are the same language

        $last_assignment_query = $wpdb->get_results( "
            SELECT meta_value as user, MAX(hist_time) as assignment_date
            from $wpdb->dt_activity_log as log
            WHERE meta_key = 'assigned_to'
            GROUP by meta_value",
        ARRAY_A );
        $last_assignments =[];
        foreach ( $last_assignment_query as $assignment ){
            $user_id = str_replace( 'user-', '', $assignment["user"] );
            $last_assignments[$user_id] = $assignment["assignment_date"];
        }

        $location_data = [];
        if ( isset( $params["location_ids"] ) ) {
            foreach ( $params["location_ids"] as $grid_id ){
                $location = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->dt_location_grid WHERE grid_id = %s", esc_sql( $grid_id ) ), ARRAY_A );
                $levels = [];

                if ( $grid_id === "1" ){
                    $match_location_ids = "( 1 )";
                } else {
                    $match_location_ids = "( ";
                    for ( $i = 0; $i <= ( (int) $location["level"] ); $i++ ) {
                        $levels[ $location["admin". $i . "_grid_id"]] = [ "level" => $i ];
                        $match_location_ids .= $location["admin". $i . "_grid_id"] . ', ';
                    }
                    $match_location_ids .= ')';

                }

                $match_location_ids = str_replace( ', )', ' )', $match_location_ids );
                //phpcs:disable
                //already sanitized IN value
                $location_names = $wpdb->get_results( "
                    SELECT alt_name, grid_id
                    FROM $wpdb->dt_location_grid
                    WHERE grid_id IN $match_location_ids
                ", ARRAY_A);

                //get users with the same location grid.
                $users_in_location = $wpdb->get_results( "
                    SELECT pm.meta_value as user_id, loc.meta_value as grid_id
                    FROM $wpdb->postmeta loc
                    JOIN $wpdb->postmeta pm ON ( pm.post_id = loc.post_id AND pm.meta_key = 'corresponds_to_user' )
                    WHERE loc.meta_key = 'location_grid' AND loc.meta_value IN $match_location_ids
                ", ARRAY_A );
                //phpcs:enable

                foreach ( $location_names as $l ){
                    if ( isset( $levels[$l["grid_id"]] ) ) {
                        $levels[$l["grid_id"]]["name"] = $l["alt_name"];
                    }
                }
                //0 if the location is exact match. 1 if the matched location is the parent etc
                foreach ( $users_in_location as $l ){
                    $level = (int) $location["level"] - $levels[$l["grid_id"]]["level"];
                    if ( !isset( $location_data[$l["user_id"]] ) || $location_data[$l["user_id"]] > $level ){
                        $location_data[$l["user_id"]] = [
                            "level" => $level,
                            "match_name" => $levels[$l["grid_id"]]["name"]
                        ];
                    }
                }
            }
        }


        $list = [];
        $workload_status_options = dt_get_site_custom_lists()["user_workload_status"] ?? [];
        foreach ( $user_data as $user ) {
            $roles = maybe_unserialize( $user["roles"] );
            if ( isset( $roles["multiplier"] ) || isset( $roles["dt_admin"] ) || isset( $roles["dispatcher"] ) || isset( $roles["marketer"] )) {
                $u = [
                    "name" => $user["display_name"],
                    "ID" => $user["ID"],
                    "avatar" => get_avatar_url( $user["ID"], [ 'size' => '16' ] ),
                    "last_assignment" => $last_assignments[$user["ID"]] ?? null,
                    "roles" => array_keys( $roles ),
                    "location" => null,
                    "languages" => [],
                ];
                $user_languages = get_user_option( "user_languages", $user["ID"] );
                if ( $user_languages ) {
                    $u["languages"] = $user_languages;
                }
                //extra information for the dispatcher
                $workload_status = $user["workload_status"] ?? null;
                if ( $workload_status && isset( $workload_status_options[$workload_status]["color"] ) ) {
                    $u['status'] = $workload_status;
                    $u['status_color'] = $workload_status_options[$workload_status]["color"];
                }
                if ( isset( $location_data[$user["ID"]] ) ){
                    $u["location"] = $location_data[$user["ID"]]["level"];
                    $u["best_location_match"] = $location_data[$user["ID"]]["match_name"];
                }

                $u["update_needed"] = (int) $user["number_update"] ?? 0;

                $list[] = $u;
            }
        }

        return $list;
    }
}
