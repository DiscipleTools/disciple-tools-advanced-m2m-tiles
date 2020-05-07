<?php


class DT_Roles_Banners {
    public $js_file = 'roles.js';

    public function __construct() {
        $path = dt_get_url_path();
        //only load if on the details page
        if ( strpos( $path, 'contacts' ) === 0 && $path !== 'contacts' ){
            add_action( 'dt_contact_detail_notification', [ $this, 'dt_banners' ], 10, 1 );
            add_action( 'wp_enqueue_scripts', [ $this, 'scripts' ], 99 );
        }
    }

    public function scripts() {
        wp_enqueue_script( 'dt_roles_script', trailingslashit( plugin_dir_url( __FILE__ ) ) . $this->js_file, [], filemtime( plugin_dir_path( __FILE__ ) . $this->js_file ), true );
        wp_localize_script(
            'dt_roles_script', 'roles_settings', [
                "template_dir_uri" => get_template_directory_uri(),
                "translations" => [
                    "all" => __( "All", 'roles_plugin' ),
                    "ready" => __( "Ready", 'roles_plugin' ),
                    "recent" => __( "Recent", 'roles_plugin' ),
                    "location" => __( "Location", 'roles_plugin' ),
                    "assign" => __( "Assign", 'roles_plugin' ),
                ],
                "dispatcher_id" => dt_get_base_user( true )
            ]
        );
    }

    public function dt_banners( $contact ){
        if ( dt_current_user_has_role( 'dispatcher' ) ){
            $field_settings = apply_filters( "dt_get_post_type_settings", [], "contacts" )["fields"];
            ?>
            <section class="small-12 grid-y grid-margin-y cell dispatcher-tile">
                <div class="bordered-box">
                    <div class="cell dt-filter-tabs">
                        <h4 class="section-header"><?php esc_html_e( 'Assign For', 'roles_plugin' ); ?> <span id="dispatch-tile-loader" style="display: inline-block; margin-left: 10px" class="loading-spinner"></span>
                            <button class="section-chevron chevron_down">
                                <img src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/chevron_down.svg' ) ?>"/>
                            </button>
                            <button class="section-chevron chevron_up">
                                <img src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/chevron_up.svg' ) ?>"/>
                            </button>
                        </h4>
                        <div class="section-body">
                            <ul class="horizontal tabs" data-tabs id="filter-tabs">
                                <?php if ( isset( $field_settings['reason_assigned_to']["default"] ) ) :
                                    foreach ( $field_settings['reason_assigned_to']["default"] as $key => $value ) : ?>
                                        <li class="tabs-title">
                                            <a href="#<?php echo esc_html( $key ); ?>" data-field="<?php echo esc_html( $key ); ?>">
                                                <?php echo esc_html( $value["label"] ); ?>
                                            </a>
                                        </li>
                                    <?php endforeach;
                                endif;?>
<!--                                <li class="tabs-title">-->
<!--                                    <a href="#other" data-field="other">Other</a>-->
<!--                                </li>-->
                            </ul>

                            <div class="tabs-column-right users-select-panel" style="margin-top:20px; display: none">
                                <div id="defined-lists" style="padding-top:0">
                                    <div class="grid-x grid-margin-x" style="margin-top:5px">
                                        <div class="medium-4 cell">

                                            <div class="input-group">
                                                <input id="search-users-input" class="input-group-field" type="text" placeholder="Multipliers">
                                                <div class="input-group-button">
                                                    <button type="button" class="button hollow"><i class="fi-magnifying-glass"></i></button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="medium-8 cell">
                                            <div id="user-list-filters" style="margin-bottom:3px">
                                                <!--filters is filled out by js-->
                                            </div>
                                            <div class="populated-list">
                                                <!--users list is filled out by js-->

                                            </div>
                                        </div>

                                    </div>
                                </div>
                                <div class="" id="other-assign-to-typeahead" style="display:none;">
                                    <strong><?php esc_html_e( 'Search', 'roles_plugin' ); ?></strong><br>
                                    <div class="">
                                        <var id="assign-result-container" class="result-container assign-result-container"></var>
                                        <div id="assign_t" name="form-assign">
                                            <div class="typeahead__container">
                                                <div class="typeahead__field">
                                                    <span class="typeahead__query">
                                                        <input class="js-typeahead-assign input-height" dir="auto"
                                                               name="assign[query]" placeholder="<?php echo esc_html_x( "Search Users", 'input field placeholder', 'roles_plugin' ) ?>"
                                                               autocomplete="off">
                                                    </span>
                                                    <span class="typeahead__button">
                                                        <button type="button" class="typeahead__image_button input-height" disabled>
                                                            <i class="fi-magnifying-glass"></i>
                                                        </button>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
            </section>


            <style type="text/css">
                .dispatcher-tile .populated-list .assigned-to-row > span {
                    white-space: nowrap;
                    text-overflow: ellipsis;
                    overflow: hidden;
                }
                .dispatcher-tile .populated-list .assigned-to-row:hover {
                     background-color: #F2F2F2;
                }
                .dispatcher-tile .populated-list .assigned-to-row {
                    padding: 5px 5px 0 5px;
                    border-bottom: 1px solid rgba(128, 128, 128, 0.31);
                }
                .dispatcher-tile .populated-list {
                    overflow-y: scroll;
                    max-height: 250px;
                }

            </style>
            <?php
        }

        /**
         * My actions tile
         */
        if ( dt_current_user_has_role( 'marketer' ) ) {
            ?>
            <section class="small-12 grid-y grid-margin-y cell dr-tile">
                <div class="bordered-box">
                    <div style="display: flex">
                        <div>
                            <h4 class="section-header"><?php esc_html_e( 'My actions', 'roles_plugin' ); ?> <span id="dr-tile-loader" style="display: inline-block; margin-left: 10px; margin-right: 10px" class="loading-spinner"></span></h4>
                        </div>
                        <div class="action-buttons">
                            <button id="mark_dispatch_needed" class="button hollow"><?php esc_html_e( 'Ready for Dispatch', 'roles_plugin' ); ?></button>
                            <button id="claim" class="button hollow"><?php esc_html_e( 'Assign to me for follow-up', 'roles_plugin' ); ?></button>
                        </div>
                    </div>
                </div>
            </section>
            <style type="text/css">
                .action-buttons .button{
                    margin-bottom: 0;
                }
            </style>


            <?php

        }
    }
}
new DT_Roles_Banners();


