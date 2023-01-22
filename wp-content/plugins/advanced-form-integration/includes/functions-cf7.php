<?php

add_action( 'wpcf7_submit', 'adfoin_cf7_submission', 10, 2 );

/*
 * Contact Form 7 Submission
 */
function adfoin_cf7_submission( $contact_form, $result ) {

    if( 'validation_failed' == $result['status'] || 'spam' == $result['status'] ) {
        return;
    }
    
    @set_time_limit(300);

    global $post;

    $submission  = WPCF7_Submission::get_instance();
    $posted_data = $submission->get_posted_data();
    $form_id     = $contact_form->id();

    $post_id = (int) $submission->get_meta( 'container_post_id' );

    if( $post_id) {
        $post = get_post( $post_id, 'OBJECT' );
    }

    $special_tag_values = adfoin_get_special_tags_values( $post );

    if( is_array( $posted_data ) && is_array( $special_tag_values ) ) {
        $posted_data = $posted_data + $special_tag_values;
    }

    $posted_data["submission_date"] = date( "Y-m-d H:i:s" );
    $posted_data["form_id"]         = $form_id;
    $posted_data["form_name"]       = $contact_form->title();

    global $wpdb;

    $saved_records = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}adfoin_integration WHERE status = 1 AND form_provider = 'cf7' AND form_id = ".$form_id , ARRAY_A );

    foreach ( $saved_records as $record ) {
        $action_provider = $record["action_provider"];

        call_user_func( "adfoin_{$action_provider}_send_data", $record, $posted_data );
    }
}

/*
 * Get Forms list
 */
function adfoin_cf7_get_forms( $form_provider ) {
    if( $form_provider != 'cf7' ) {
        return;
    }

    $args     = array( 'post_type' => 'wpcf7_contact_form', 'posts_per_page' => -1 );
    $cf7Forms = get_posts( $args );
    $forms    = wp_list_pluck( $cf7Forms, 'post_title', 'ID' );

    return $forms;
}

/*
 * Get form fields
 */
function adfoin_cf7_get_form_fields( $form_provider, $form_id ) {
    if( $form_provider != 'cf7' ) {
        return;
    }

    $ContactForm  = WPCF7_ContactForm::get_instance( $form_id );
    $form_fields  = $ContactForm->scan_form_tags();
    $final_fields = array_filter( wp_list_pluck( $form_fields, "name", "name" ) );

    $special_tags = adfoin_get_special_tags();

    if( is_array( $final_fields ) && is_array( $special_tags ) ) {
        $final_fields = $final_fields + $special_tags;
    }

    $final_fields["form_id"]         = __( "Form ID", "advanced-form-integration" );
    $final_fields["form_name"]       = __( "Form Name", "advanced-form-integration" );
    return $final_fields;
}