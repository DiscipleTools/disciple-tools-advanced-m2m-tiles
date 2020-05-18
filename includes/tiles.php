<?php


class DT_Roles_Banners {
    public $js_file = 'roles.js';
    public $plugin_url;

    public function __construct() {
        $path = dt_get_url_path();
        $this->plugin_url = trailingslashit( plugin_dir_url( __FILE__ ) );
        //only load if on the details page
        if ( strpos( $path, 'contacts' ) === 0 && $path !== 'contacts' ){
            add_action( 'dt_contact_detail_notification', [ $this, 'dt_banners' ], 10, 1 );
            add_action( 'wp_enqueue_scripts', [ $this, 'scripts' ], 99 );
            add_action( 'dt_record_top_full_with', [ $this, 'top_tile' ], 10, 2 );
        }
    }

    public function scripts() {
        wp_enqueue_script( 'dt_roles_script', $this->plugin_url . $this->js_file, [], filemtime( plugin_dir_path( __FILE__ ) . $this->js_file ), true );
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
        $roles_settings = get_option( "dt_roles_settings", [] );
        if ( dt_current_user_has_role( 'dispatcher' ) && isset( $roles_settings["assigned_to"]["enabled"] ) && $roles_settings["assigned_to"]["enabled"] !== false ) {
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
                                                <img src="<?php echo esc_url( $value['icon'] ); ?>"
                                                ><?php echo esc_html( $value["label"] ); ?>
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
                #filter-tabs img {
                    height: 20px;
                    width: 20px;
                    display: inline-block;
                    vertical-align: middle;
                    margin-right: 2px;
                }
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
    }

    public function top_tile( $post_type, $contact ){
        if ( $post_type === "contacts" ) {
            $roles_settings = get_option( "dt_roles_settings", [] );
            if ( isset( $roles_settings["my_actions"]["enabled"] ) && $roles_settings["my_actions"]["enabled"] !== false
                && dt_current_user_has_role( "multiplier" )
                && dt_current_user_has_role( "marketer" ) )
            {
                $contact_fields = Disciple_Tools_Contacts::get_contact_fields(); ?>
                <section class="small-12 cell">
                    <div class="bordered-box" id="action-bar">
                        <div class="record-name" title="<?php the_title_attribute(); ?>" style="display: flex">
                            <div class="title"><?php the_title_attribute(); ?></div>
                            <span id="action-bar-loader" style="display: inline-block; margin-left: 10px" class="loading-spinner"></span>
                            <button class="expand-text-descriptions">
                                <img src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/chevron_down.svg' ) ?>"/>
                            </button>
                            <button class="expand-text-descriptions" style="display: none">
                                <img src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/chevron_up.svg' ) ?>"/>
                            </button>
                        </div>
                        <!--                    <span class="separator"></span>-->
                        <?php do_action( "dt_record_actions_bar_buttons_start", $post_type, $contact, $roles_settings ) ?>
                        <?php if ( $contact["assigned_to"]["id"] != get_current_user_id() && dt_current_user_has_role( "multiplier" ) ) : ?>
                            <button class="action-button" id="claim">
                                <img src="<?php echo esc_url( $this->plugin_url . "images/volunteer.svg" ); ?>"
                                ><span class="action-text"><?php esc_html_e( 'Claim for follow-up', 'roles_plugin' ); ?></span>
                            </button>
                        <?php endif; ?>
                        <?php if ( dt_current_user_has_role( 'marketer' ) ) : ?>
                            <button class="action-button" id="mark_dispatch_needed">
                                <img src="<?php echo esc_url( $this->plugin_url . "images/arrow-check-up-solid.svg" ); ?>"
                                ><span class="action-text"><?php esc_html_e( 'Ready for Dispatch', 'roles_plugin' ); ?></span>
                            </button>
                        <?php endif; ?>
                        <span class="separator"></span>
                        <?php if ( dt_current_user_has_role( "multiplier" ) ):
                            foreach ( $contact_fields as $field => $val ) {
                                if ( strpos( $field, "quick_button" ) === 0 ) {
                                    $current_value = 0;
                                    if ( isset( $contact[ $field ] ) ) {
                                        $current_value = $contact[ $field ];
                                    }
                                    $val["icon"] = $val["icon"] ?? 'meeting.svg'
                                    ?>
                                    <button data-id="<?php echo esc_html( $field ); ?>"
                                         data-count="<?php echo esc_html( $current_value ); ?>"
                                         class="action-button quick-action"
                                         title="<?php echo esc_html( $val['name'] ); ?>"
                                    >
                                        <img class="dt-svg-black" src="<?php echo esc_url( $val['icon'] ); ?>"
                                        ><span class="action-text"><?php echo esc_html( $val["short_name"] ?? $val["name"] ); ?></span>
                                    </button>
                                <?php }
                            }
                        endif; ?>
                        <?php do_action( "dt_record_actions_bar_buttons_end", $post_type, $contact, $roles_settings ) ?>
                    </div>
                </section>

                <style type="text/css">
                    .dt-svg-blue {
                        filter: invert(33%) sepia(95%) saturate(298%) hue-rotate(164deg) brightness(101%) contrast(87%);
                    }
                    .dt-svg-black {
                        filter: brightness(0);
                    }
                    .dt-svg-grey {
                        filter: invert(22%) sepia(0%) saturate(0%) hue-rotate(223deg) brightness(101%) contrast(84%);
                    }
                    #action-bar {
                        padding: 3px 1rem;
                        border-radius: 5px;
                        display: flex;
                        flex-wrap: wrap;

                    }
                    #action-bar .record-name {
                        max-width: 20%;
                        margin: 3px 0;
                        color: #444;
                    }
                    #action-bar .record-name .title {
                        font-weight: bold;
                        white-space: nowrap;
                        overflow: hidden;
                        text-overflow: ellipsis;
                        line-height: 30px;

                    }
                    #action-bar .expand-text-descriptions {
                        display: none;
                        width: 20px;
                        img {
                        }
                    }
                    @media only screen and (max-width: 640px) {
                        #action-bar .record-name {
                            width: 100%;
                            max-width: 100%;
                            display: block;
                        }
                        #action-bar .record-name .title {
                            flex-grow: 1;
                        }
                        #action-bar .action-text {
                            display: none;
                        }
                        #action-bar .expand-text-descriptions {
                            display: block;
                        }
                    }
                    #action-bar img {
                        height: 25px;
                        width: 25px;
                        display: inline-block;
                        vertical-align: middle;
                        filter: invert(22%) sepia(0%) saturate(0%) hue-rotate(223deg) brightness(101%) contrast(84%);
                    }
                    #action-bar .action-button {
                        line-height: 30px;
                        height: 30px;
                        text-align: center;
                        margin: 3px 6px;
                        padding:0;
                        border-radius: 5px;
                        color: #444;
                    }
                    #action-bar .action-button:hover {
                        background-color: #eee;
                    }
                    #action-bar span {
                        display: inline-block;
                        vertical-align: middle;
                        line-height: normal;
                    }
                    #action-bar .separator {
                        border-right: 1px solid;
                        color: #444;
                        margin: 3px 2px
                    }
                    #mobile-quick-actions {
                        display: none;
                    }
                </style>
            <?php }
        }

    }

}
new DT_Roles_Banners();


