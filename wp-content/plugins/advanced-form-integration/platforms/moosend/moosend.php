<?php

add_filter(
    'adfoin_action_providers',
    'adfoin_moosend_actions',
    10,
    1
);
function adfoin_moosend_actions( $actions )
{
    $actions['moosend'] = array(
        'title' => __( 'Moosend', 'advanced-form-integration' ),
        'tasks' => array(
        'subscribe' => __( 'Subscribe To List', 'advanced-form-integration' ),
    ),
    );
    return $actions;
}

add_filter(
    'adfoin_settings_tabs',
    'adfoin_moosend_settings_tab',
    10,
    1
);
function adfoin_moosend_settings_tab( $providers )
{
    $providers['moosend'] = __( 'Moosend', 'advanced-form-integration' );
    return $providers;
}

add_action(
    'adfoin_settings_view',
    'adfoin_moosend_settings_view',
    10,
    1
);
function adfoin_moosend_settings_view( $current_tab )
{
    if ( $current_tab != 'moosend' ) {
        return;
    }
    $nonce = wp_create_nonce( "adfoin_moosend_settings" );
    $api_token = ( get_option( 'adfoin_moosend_api_token' ) ? get_option( 'adfoin_moosend_api_token' ) : "" );
    ?>

    <form name="moosend_save_form" action="<?php 
    echo  esc_url( admin_url( 'admin-post.php' ) ) ;
    ?>"
          method="post" class="container">

        <input type="hidden" name="action" value="adfoin_moosend_save_api_token">
        <input type="hidden" name="_nonce" value="<?php 
    echo  $nonce ;
    ?>"/>

        <table class="form-table">
            <tr valign="top">
                <th scope="row"> <?php 
    _e( 'API Key', 'advanced-form-integration' );
    ?></th>
                <td>
                    <input type="text" name="adfoin_moosend_api_token"
                           value="<?php 
    echo  esc_attr( $api_token ) ;
    ?>" placeholder="<?php 
    _e( 'Please enter API Token', 'advanced-form-integration' );
    ?>"
                           class="regular-text"/>
                    <p class="description" id="code-description"><?php 
    _e( 'Go to Account > API Key to get API Key', 'advanced-form-integration' );
    ?></p>
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
    'admin_post_adfoin_moosend_save_api_token',
    'adfoin_save_moosend_api_token',
    10,
    0
);
function adfoin_save_moosend_api_token()
{
    // Security Check
    if ( !wp_verify_nonce( $_POST['_nonce'], 'adfoin_moosend_settings' ) ) {
        die( __( 'Security check Failed', 'advanced-form-integration' ) );
    }
    $api_token = sanitize_text_field( $_POST["adfoin_moosend_api_token"] );
    // Save tokens
    update_option( "adfoin_moosend_api_token", $api_token );
    advanced_form_integration_redirect( "admin.php?page=advanced-form-integration-settings&tab=moosend" );
}

add_action(
    'adfoin_add_js_fields',
    'adfoin_moosend_js_fields',
    10,
    1
);
function adfoin_moosend_js_fields( $field_data )
{
}

add_action( 'adfoin_action_fields', 'adfoin_moosend_action_fields' );
function adfoin_moosend_action_fields()
{
    ?>
    <script type="text/template" id="moosend-action-template">
        <table class="form-table">
            <tr valign="top" v-if="action.task == 'subscribe'">
                <th scope="row">
                    <?php 
    esc_attr_e( 'Map Fields', 'advanced-form-integration' );
    ?>
                </th>
                <td scope="row">

                </td>
            </tr>

            <tr valign="top" class="alternate" v-if="action.task == 'subscribe'">
                <td scope="row-title">
                    <label for="tablecell">
                        <?php 
    esc_attr_e( 'Moosend List', 'advanced-form-integration' );
    ?>
                    </label>
                </td>
                <td>
                    <select name="fieldData[listId]" v-model="fielddata.listId" required="required">
                        <option value=""> <?php 
    _e( 'Select List...', 'advanced-form-integration' );
    ?> </option>
                        <option v-for="(item, index) in fielddata.list" :value="index" > {{item}}  </option>
                    </select>
                    <div class="spinner" v-bind:class="{'is-active': listLoading}" style="float:none;width:auto;height:auto;padding:10px 0 10px 50px;background-position:20px 0;"></div>
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
        printf( __( 'To unlock custom fields consider <a href="%s">upgrading to Pro</a>.', 'advanced-form-integration' ), admin_url( 'admin.php?page=advanced-form-integration-settings-pricing' ) );
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

add_action(
    'wp_ajax_adfoin_get_moosend_list',
    'adfoin_get_moosend_list',
    10,
    0
);
/*
 * Get Kalviyo subscriber lists
 */
function adfoin_get_moosend_list()
{
    // Security Check
    if ( !wp_verify_nonce( $_POST['_nonce'], 'advanced-form-integration' ) ) {
        die( __( 'Security check Failed', 'advanced-form-integration' ) );
    }
    $api_token = get_option( "adfoin_moosend_api_token" );
    if ( !$api_token ) {
        return array();
    }
    $args = array(
        'headers' => array(
        'Content-Type' => 'application/json',
    ),
    );
    $url = "https://api.moosend.com/v3/lists.json?PageSize=1000&apikey=" . $api_token;
    $data = wp_remote_request( $url, $args );
    if ( is_wp_error( $data ) ) {
        wp_send_json_error();
    }
    $body = json_decode( $data["body"] );
    $lists = wp_list_pluck( $body->Context->MailingLists, 'Name', 'ID' );
    wp_send_json_success( $lists );
}

/*
 * Saves connection mapping
 */
function adfoin_moosend_save_integration()
{
    $params = array();
    parse_str( adfoin_sanitize_text_or_array_field( $_POST['formData'] ), $params );
    $trigger_data = ( isset( $_POST["triggerData"] ) ? adfoin_sanitize_text_or_array_field( $_POST["triggerData"] ) : array() );
    $action_data = ( isset( $_POST["actionData"] ) ? adfoin_sanitize_text_or_array_field( $_POST["actionData"] ) : array() );
    $field_data = ( isset( $_POST["fieldData"] ) ? adfoin_sanitize_text_or_array_field( $_POST["fieldData"] ) : array() );
    $integration_title = ( isset( $trigger_data["integrationTitle"] ) ? $trigger_data["integrationTitle"] : "" );
    $form_provider_id = ( isset( $trigger_data["formProviderId"] ) ? $trigger_data["formProviderId"] : "" );
    $form_id = ( isset( $trigger_data["formId"] ) ? $trigger_data["formId"] : "" );
    $form_name = ( isset( $trigger_data["formName"] ) ? $trigger_data["formName"] : "" );
    $action_provider = ( isset( $action_data["actionProviderId"] ) ? $action_data["actionProviderId"] : "" );
    $task = ( isset( $action_data["task"] ) ? $action_data["task"] : "" );
    $type = ( isset( $params["type"] ) ? $params["type"] : "" );
    $all_data = array(
        'trigger_data' => $trigger_data,
        'action_data'  => $action_data,
        'field_data'   => $field_data,
    );
    global  $wpdb ;
    $integration_table = $wpdb->prefix . 'adfoin_integration';
    if ( $type == 'new_integration' ) {
        $result = $wpdb->insert( $integration_table, array(
            'title'           => $integration_title,
            'form_provider'   => $form_provider_id,
            'form_id'         => $form_id,
            'form_name'       => $form_name,
            'action_provider' => $action_provider,
            'task'            => $task,
            'data'            => json_encode( $all_data, true ),
            'status'          => 1,
        ) );
    }
    
    if ( $type == 'update_integration' ) {
        $id = esc_sql( trim( $params['edit_id'] ) );
        if ( $type != 'update_integration' && !empty($id) ) {
            return;
        }
        $result = $wpdb->update( $integration_table, array(
            'title'         => $integration_title,
            'form_provider' => $form_provider_id,
            'form_id'       => $form_id,
            'form_name'     => $form_name,
            'data'          => json_encode( $all_data, true ),
        ), array(
            'id' => $id,
        ) );
    }
    
    
    if ( $result ) {
        wp_send_json_success();
    } else {
        wp_send_json_error();
    }

}

/*
 * Handles sending data to moosend API
 */
function adfoin_moosend_send_data( $record, $posted_data )
{
    $api_token = ( get_option( 'adfoin_moosend_api_token' ) ? get_option( 'adfoin_moosend_api_token' ) : "" );
    if ( !$api_token ) {
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
    $list_id = $data["listId"];
    $task = $record["task"];
    
    if ( $task == "subscribe" ) {
        $email = ( empty($data["email"]) ? "" : adfoin_get_parsed_values( $data["email"], $posted_data ) );
        $name = ( empty($data["name"]) ? "" : adfoin_get_parsed_values( $data["name"], $posted_data ) );
        $data = array(
            'email' => $email,
            'name'  => $name,
        );
        $url = "https://api.moosend.com/v3/subscribers/{$list_id}/subscribe.json?apikey={$api_token}";
        $args = array(
            'timeout' => 30,
            'headers' => array(
            'Content-Type' => 'application/json',
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
