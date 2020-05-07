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
    }
    return $fields;
}