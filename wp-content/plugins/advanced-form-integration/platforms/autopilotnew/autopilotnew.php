<?php

add_filter(
    'adfoin_action_providers',
    'adfoin_autopilotnew_actions',
    10,
    1
);
function adfoin_autopilotnew_actions( $actions )
{
    $actions['autopilotnew'] = array(
        'title' => __( 'Ortto', 'advanced-form-integration' ),
        'tasks' => array(
        'subscribe' => __( 'Add/Update Person', 'advanced-form-integration' ),
    ),
    );
    return $actions;
}

add_filter(
    'adfoin_settings_tabs',
    'adfoin_autopilotnew_settings_tab',
    10,
    1
);
function adfoin_autopilotnew_settings_tab( $providers )
{
    $providers['autopilotnew'] = __( 'Ortto', 'advanced-form-integration' );
    return $providers;
}

add_action(
    'adfoin_settings_view',
    'adfoin_autopilotnew_settings_view',
    10,
    1
);
function adfoin_autopilotnew_settings_view( $current_tab )
{
    if ( $current_tab != 'autopilotnew' ) {
        return;
    }
    $nonce = wp_create_nonce( "adfoin_autopilotnew_settings" );
    $api_key = ( get_option( 'adfoin_autopilotnew_api_key' ) ? get_option( 'adfoin_autopilotnew_api_key' ) : "" );
    ?>

    <form name="autopilotnew_save_form" action="<?php 
    echo  esc_url( admin_url( 'admin-post.php' ) ) ;
    ?>"
          method="post" class="container">

        <input type="hidden" name="action" value="adfoin_autopilotnew_save_api_key">
        <input type="hidden" name="_nonce" value="<?php 
    echo  $nonce ;
    ?>"/>

        <table class="form-table">
            <tr valign="top">
                <th scope="row"> <?php 
    _e( 'Private Key', 'advanced-form-integration' );
    ?></th>
                <td>
                    <input type="text" name="adfoin_autopilotnew_api_key"
                           value="<?php 
    echo  esc_attr( $api_key ) ;
    ?>" placeholder="<?php 
    _e( 'Enter Private Key', 'advanced-form-integration' );
    ?>"
                           class="regular-text"/>
                    <p class="description" id="code-description"><?php 
    _e( '1. Create a new data source.<br> 2. Select Custom API.<br> 3. Put a name.<br> 4. Copy private key.<br> 5. Save data source. ', 'advanced-form-integration' );
    ?></a></p>
                </td>
            </tr>
        </table>
        <?php 
    submit_button();
    ?>
    </form>

    <?php 
}

add_action(
    'admin_post_adfoin_autopilotnew_save_api_key',
    'adfoin_save_autopilotnew_api_key',
    10,
    0
);
function adfoin_save_autopilotnew_api_key()
{
    // Security Check
    if ( !wp_verify_nonce( $_POST['_nonce'], 'adfoin_autopilotnew_settings' ) ) {
        die( __( 'Security check Failed', 'advanced-form-integration' ) );
    }
    $api_key = sanitize_text_field( $_POST["adfoin_autopilotnew_api_key"] );
    // Save tokens
    update_option( "adfoin_autopilotnew_api_key", $api_key );
    advanced_form_integration_redirect( "admin.php?page=advanced-form-integration-settings&tab=autopilotnew" );
}

add_action( 'adfoin_action_fields', 'adfoin_autopilotnew_action_fields' );
function adfoin_autopilotnew_action_fields()
{
    ?>
    <script type="text/template" id="autopilotnew-action-template">
        <table class="form-table">
            <tr valign="top" v-if="action.task == 'subscribe'">
                <th scope="row">
                    <?php 
    esc_attr_e( 'Person Fields', 'advanced-form-integration' );
    ?>
                </th>
                <td scope="row">

                </td>
            </tr>

            <editable-field v-for="field in fields" v-bind:key="field.value" v-bind:field="field" v-bind:trigger="trigger" v-bind:action="action" v-bind:fielddata="fielddata"></editable-field>
            <?php 
    
    if ( adfoin_fs()->is_not_paying() ) {
        ?>
                    <tr valign="top" v-if="action.task == 'subscribe'">
                        <th scope="row">
                            <?php 
        esc_attr_e( 'Go Pro', 'advanced-form-integration' );
        ?>
                        </th>
                        <td scope="row">
                            <span><?php 
        printf( __( 'To unlock custom fields, consider <a href="%s">upgrading to Pro</a>.', 'advanced-form-integration' ), admin_url( 'admin.php?page=advanced-form-integration-settings-pricing' ) );
        ?></span>
                        </td>
                    </tr>
                    <?php 
    }
    
    ?>
        </table>
    </script>
    <?php 
}

/*
 * Handles sending data to Autopilot API
 */
function adfoin_autopilotnew_send_data( $record, $posted_data )
{
    $api_key = ( get_option( 'adfoin_autopilotnew_api_key' ) ? get_option( 'adfoin_autopilotnew_api_key' ) : "" );
    if ( !$api_key ) {
        return;
    }
    $record_data = json_decode( $record["data"], true );
    if ( array_key_exists( "cl", $record_data["action_data"] ) ) {
        if ( $record_data["action_data"]["cl"]["active"] == "yes" ) {
            if ( !adfoin_match_conditional_logic( $record_data["action_data"]["cl"], $posted_data ) ) {
                return;
            }
        }
    }
    $data = $record_data["field_data"];
    $task = $record["task"];
    
    if ( $task == "subscribe" ) {
        $email = ( empty($data["email"]) ? "" : adfoin_get_parsed_values( $data["email"], $posted_data ) );
        $first_name = ( empty($data["firstName"]) ? "" : adfoin_get_parsed_values( $data["firstName"], $posted_data ) );
        $last_name = ( empty($data["lastName"]) ? "" : adfoin_get_parsed_values( $data["lastName"], $posted_data ) );
        $url = "https://api.ap3api.com/v1/person/merge";
        $data = array(
            "people"         => array( array(
            "fields" => array(
            "str::email" => $email,
        ),
        ) ),
            "async"          => false,
            "merge_by"       => array( "str::email" ),
            "merge_strategy" => 2,
            "find_strategy"  => 0,
        );
        if ( $first_name ) {
            $data["people"][0]["fields"]["str::first"] = $first_name;
        }
        if ( $last_name ) {
            $data["people"][0]["fields"]["str::last"] = $last_name;
        }
        $args = array(
            'headers' => array(
            'X-Api-Key' => $api_key,
        ),
            'body'    => json_encode( $data ),
        );
        $return = wp_remote_post( $url, $args );
        adfoin_add_to_log(
            $return,
            $url,
            $args,
            $record
        );
    }
    
    return;
}
