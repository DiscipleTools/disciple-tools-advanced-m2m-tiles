<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

add_filter( "dt_custom_fields_settings", "custom_fields", 1, 2 );


function custom_fields( array $fields, string $post_type = "" ){
    if ( $post_type === "contacts" ) {
        $fields["reason_assigned_to"] = [
            'name' => __( 'Reason Assigned To', 'roles_plugin' ),
            'type'        => 'key_select',
            'default' => [
                'follow-up' => [
                    'label' => __( "Follow-Up", 'roles_plugin' ),
                    'roles' => [ "multiplier" ]
                ],
                'digital-response' => [
                    'label' => __( "Digital Response", 'roles_plugin' ),
                    'roles' => [ "marketer" ]
                ],
                'dispatch' => [
                    'label' => __( "Dispatch", 'roles_plugin' ),
                    'roles' => [ "dispatcher" ]
                ],
            ],
        ];
        if ( isset( $fields["quick_button_no_answer"] ) ) {
            $fields["quick_button_no_answer"]["short_name"] = __( 'No Answer', 'disciple_tools' );
            $fields["quick_button_no_answer"]["icon"] = trailingslashit( plugin_dir_url( __FILE__ ) ) . 'images/phone-no-answer.svg';
        }
        if ( isset( $fields["quick_button_contact_established"] ) ){
            $fields["quick_button_contact_established"]["short_name"] = __( 'Talked', 'disciple_tools' );
            $fields["quick_button_contact_established"]["icon"] = trailingslashit( plugin_dir_url( __FILE__ ) ) . 'images/phone-successful.svg';
        }
        if ( isset( $fields["quick_button_meeting_scheduled"] ) ){
            $fields["quick_button_meeting_scheduled"]["short_name"] = __( 'Scheduled', 'disciple_tools' );
            $fields["quick_button_meeting_scheduled"]["icon"] = trailingslashit( plugin_dir_url( __FILE__ ) ) . 'images/calendar-clock.svg';
        }
        if ( isset( $fields["quick_button_meeting_complete"] ) ){
            $fields["quick_button_meeting_complete"]["short_name"] = __( 'Complete', 'disciple_tools' );
            $fields["quick_button_meeting_complete"]["icon"] = trailingslashit( plugin_dir_url( __FILE__ ) ) . 'images/meeting.svg';
        }
        if ( isset( $fields["quick_button_no_show"] ) ){
            $fields["quick_button_no_show"]["short_name"] = __( 'No-Show', 'disciple_tools' );
            $fields["quick_button_no_show"]["icon"] = trailingslashit( plugin_dir_url( __FILE__ ) ) . 'images/calendar-cancel.svg';
        }
//        $fields["quick_button_message_sent"] = [
//            'name'        => __( 'Sent Message', 'disciple_tools' ),
//            'type'        => 'number',
//            'default'     => 0,
//            'section'     => 'quick_buttons',
//            'icon'        => "meeting.svg",
//        ];
    }
    return $fields;
}