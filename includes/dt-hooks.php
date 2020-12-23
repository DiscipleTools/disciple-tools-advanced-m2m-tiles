<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

add_filter( "dt_custom_fields_settings", "custom_fields", 1, 2 );


function custom_fields( array $fields, string $post_type = "" ){
    if ( $post_type === "contacts" ) {
        $fields["reason_assigned_to"] = [
            'name' => __( 'Reason Assigned To', 'dt_roles_tiles' ),
            'type'        => 'key_select',
            'default' => [
                'follow-up' => [
                    'label' => __( "Follow-Up", 'dt_roles_tiles' ),
                    'roles' => [ "multiplier" ],
                    'icon' => trailingslashit( plugin_dir_url( __FILE__ ) ) . 'images/meeting.svg',
                    'status' => 'assigned'
                ],
                'digital-response' => [
                    'label' => __( "Digital Response", 'dt_roles_tiles' ),
                    'roles' => [ "marketer" ],
                    'icon' => get_template_directory_uri() . '/dt-assets/images/socialmedia.svg',
                    'status' => 'assigned'
                ],
                'dispatch' => [
                    'label' => __( "Dispatch", 'dt_roles_tiles' ),
                    'roles' => [ "dispatcher" ],
                    'icon' => trailingslashit( plugin_dir_url( __FILE__ ) ) . 'images/assign.svg',
                    'status' => 'unassigned'
                ],
            ],
        ];
        if ( isset( $fields["quick_button_no_answer"] ) ) {
            $fields["quick_button_no_answer"]["short_name"] = __( 'No Answer', 'dt_roles_tiles' );
            $fields["quick_button_no_answer"]["icon"] = trailingslashit( plugin_dir_url( __FILE__ ) ) . 'images/phone-no-answer.svg';
        }
        if ( isset( $fields["quick_button_contact_established"] ) ){
            $fields["quick_button_contact_established"]["short_name"] = __( 'Talked', 'dt_roles_tiles' );
            $fields["quick_button_contact_established"]["icon"] = trailingslashit( plugin_dir_url( __FILE__ ) ) . 'images/phone-successful.svg';
        }
        if ( isset( $fields["quick_button_meeting_scheduled"] ) ){
            $fields["quick_button_meeting_scheduled"]["short_name"] = __( 'Scheduled', 'dt_roles_tiles' );
            $fields["quick_button_meeting_scheduled"]["icon"] = trailingslashit( plugin_dir_url( __FILE__ ) ) . 'images/calendar-clock.svg';
        }
        if ( isset( $fields["quick_button_meeting_complete"] ) ){
            $fields["quick_button_meeting_complete"]["short_name"] = __( 'Complete', 'dt_roles_tiles' );
            $fields["quick_button_meeting_complete"]["icon"] = trailingslashit( plugin_dir_url( __FILE__ ) ) . 'images/meeting.svg';
        }
        if ( isset( $fields["quick_button_no_show"] ) ){
            $fields["quick_button_no_show"]["short_name"] = __( 'No-Show', 'dt_roles_tiles' );
            $fields["quick_button_no_show"]["icon"] = trailingslashit( plugin_dir_url( __FILE__ ) ) . 'images/calendar-cancel.svg';
        }
//        $fields["quick_button_message_sent"] = [
//            'name'        => __( 'Sent Message', 'dt_roles_tiles' ),
//            'type'        => 'number',
//            'default'     => 0,
//            'section'     => 'quick_buttons',
//            'icon'        => "meeting.svg",
//        ];
    }
    return $fields;
}
