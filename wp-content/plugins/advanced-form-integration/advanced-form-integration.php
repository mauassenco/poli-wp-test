<?php

/**
 * Plugin Name: Advanced Form Integration
 * Plugin URI: https://advancedformintegration.com/
 * Description: Integrates Contact Form 7, WooCommerce to various platforms including Google Sheets.
 * Author: nasirahmed
 * Author URI: https://advancedformintegration.com/
 * Version: 1.63.7
 * License: GPL2
 * Text Domain: advanced-form-integration
 * Domain Path: languages
 * Tags: Contact Form 7, WooCommerce, Google Calendar, Google Sheets, Pipedrive, active campaign, AWeber, campaign monitor, clinchpad, close.io, convertkit, curated, directiq, drip, emailoctopus, freshsales, getresponse, google sheets, jumplead, klaviyo, liondesk, mailerlite, mailify, mailjet, moonmail, moosend, omnisend, revue, Sendinblue
 * Requires at least: 3.0.1
 * Tested up to: 6.0
 * Stable tag: 6.0
 * Requires PHP: 5.4
 *
 * Released under the GPL license
 * http://www.opensource.org/licenses/gpl-license.php
 *
 * This is an add-on for WordPress
 * http://wordpress.org/
 *
 * **********************************************************************
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 * **********************************************************************
 */
// don't call the file directly
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

if ( function_exists( 'adfoin_fs' ) ) {
    adfoin_fs()->set_basename( false, __FILE__ );
} else {
    
    if ( !function_exists( 'adfoin_fs' ) ) {
        // Create a helper function for easy SDK access.
        function adfoin_fs()
        {
            global  $adfoin_fs ;
            
            if ( !isset( $adfoin_fs ) ) {
                // Include Freemius SDK.
                require_once dirname( __FILE__ ) . '/freemius/start.php';
                $adfoin_fs = fs_dynamic_init( array(
                    'id'             => '4417',
                    'slug'           => 'advanced-form-integration',
                    'type'           => 'plugin',
                    'public_key'     => 'pk_f94bb401ae01ff3a79f438df51715',
                    'is_premium'     => false,
                    'premium_suffix' => 'Professional',
                    'has_addons'     => false,
                    'has_paid_plans' => true,
                    'menu'           => array(
                    'slug'    => 'advanced-form-integration-settings',
                    'support' => false,
                    'parent'  => array(
                    'slug' => 'advanced-form-integration',
                ),
                ),
                    'is_live'        => true,
                ) );
            }
            
            return $adfoin_fs;
        }
        
        // Init Freemius.
        adfoin_fs();
        // Signal that SDK was initiated.
        do_action( 'adfoin_fs_loaded' );
    }
    
    /**
     * Advanced Form Integration Main Class
     */
    class Advanced_Form_Integration
    {
        /**
         * Plugin Version
         *
         * @var  string
         */
        public  $version = '1.63.7' ;
        /**
         * Initializes the Advanced_Form_Integration class
         *
         * Checks for an existing Advanced_Form_Integration instance
         * and if it doesn't find one, creates it.
         *
         * @since 1.0.0
         * @return mixed | bool
         */
        public static function init()
        {
            static  $instance = false ;
            if ( !$instance ) {
                $instance = new Advanced_Form_Integration();
            }
            return $instance;
        }
        
        /**
         * Constructor for the Advanced_Form_Integration class
         *
         * Sets up all the appropriate hooks and actions
         *
         * @since 1.0
         * @return void
         */
        public function __construct()
        {
            register_activation_hook( __FILE__, [ $this, 'activate' ] );
            register_deactivation_hook( __FILE__, [ $this, 'deactivate' ] );
            add_action(
                'wp_insert_site',
                array( $this, 'new_site_added' ),
                10,
                6
            );
            $this->init_plugin();
        }
        
        /**
         * Initialize plugin
         *
         * @since 1.0.0
         * @return void
         */
        public function init_plugin()
        {
            /* Define constats */
            $this->define_constants();
            /* Include files */
            $this->includes();
            /* Instantiate classes */
            $this->init_classes();
            /* Initialize the action hooks */
            $this->init_actions();
            /* Initialize the filter hooks */
            $this->init_filters();
        }
        
        /**
         * Placeholder for activation function
         *
         * @since 1.0
         * @return void
         */
        public function activate( $networkwide )
        {
            if ( function_exists( 'is_multisite' ) && is_multisite() ) {
                
                if ( $networkwide ) {
                    global  $wpdb ;
                    $blogids = $wpdb->get_col( "SELECT blog_id FROM {$wpdb->blogs}" );
                    foreach ( $blogids as $blog_id ) {
                        switch_to_blog( $blog_id );
                        $this->create_table();
                        restore_current_blog();
                    }
                    return;
                }
            
            }
            $this->create_table();
            // Create default tables when plugin activates
        }
        
        public function new_site_added( $site )
        {
            
            if ( is_plugin_active_for_network( plugin_basename( __FILE__ ) ) ) {
                switch_to_blog( $site->blog_id );
                $this->create_table();
                restore_current_blog();
            }
        
        }
        
        /**
         * Placeholder for creating tables while activationg plugin
         *
         * @since 1.0
         * @return void
         */
        private function create_table()
        {
            global  $wpdb ;
            $collate = '';
            
            if ( $wpdb->has_cap( 'collation' ) ) {
                if ( !empty($wpdb->charset) ) {
                    $collate .= "DEFAULT CHARACTER SET {$wpdb->charset}";
                }
                if ( !empty($wpdb->collate) ) {
                    $collate .= " COLLATE {$wpdb->collate}";
                }
            }
            
            $table_schema = array( "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}adfoin_integration` (\n                    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,\n                    `title` text NOT NULL,\n                    `form_provider` varchar(255) NOT NULL,\n                    `form_id` varchar(255) NOT NULL,\n                    `form_name` varchar(255) DEFAULT NULL,\n                    `action_provider` varchar(255) NOT NULL,\n                    `task` varchar(255) NOT NULL,\n                    `data` longtext DEFAULT NULL,\n                    `extra_data` longtext DEFAULT NULL,\n                    `status` int(1) NOT NULL,\n                    `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,\n                    KEY `id` (`id`)\n                ) {$collate};", "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}adfoin_log` (\n                    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,\n                    `response_code` int(3) DEFAULT NULL,\n                    `response_message` varchar(255) DEFAULT NULL,\n                    `integration_id` bigint(20) DEFAULT NULL,\n                    `request_data` longtext DEFAULT NULL,\n                    `response_data` longtext DEFAULT NULL,\n                    `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,\n                    KEY `id` (`id`)\n                ) {$collate};" );
            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
            foreach ( $table_schema as $table ) {
                dbDelta( $table );
            }
        }
        
        /**
         * Placeholder for deactivation function
         *
         * @since 1.0
         * @return void
         */
        public function deactivate()
        {
        }
        
        /**
         * Define Add-on constants
         *
         * @since 1.0
         * @return void
         */
        public function define_constants()
        {
            define( 'ADVANCED_FORM_INTEGRATION_VERSION', $this->version );
            // Plugin Version
            define( 'ADVANCED_FORM_INTEGRATION_FILE', __FILE__ );
            // Plugin Main Folder Path
            define( 'ADVANCED_FORM_INTEGRATION_PATH', dirname( ADVANCED_FORM_INTEGRATION_FILE ) );
            // Parent Directory Path
            define( 'ADVANCED_FORM_INTEGRATION_INCLUDES', ADVANCED_FORM_INTEGRATION_PATH . '/includes' );
            // Include Folder Path
            define( 'ADVANCED_FORM_INTEGRATION_URL', plugins_url( '', ADVANCED_FORM_INTEGRATION_FILE ) );
            // URL Path
            define( 'ADVANCED_FORM_INTEGRATION_ASSETS', ADVANCED_FORM_INTEGRATION_URL . '/assets' );
            // Asset Folder Path
            define( 'ADVANCED_FORM_INTEGRATION_VIEWS', ADVANCED_FORM_INTEGRATION_PATH . '/views' );
            // View Folder Path
            define( 'ADVANCED_FORM_INTEGRATION_PLATFORMS', ADVANCED_FORM_INTEGRATION_PATH . '/platforms' );
            // View Folder Path
            define( 'ADVANCED_FORM_INTEGRATION_TEMPLATES', ADVANCED_FORM_INTEGRATION_PATH . '/templates' );
            // View Folder Path
            define( 'ADVANCED_FORM_INTEGRATION_PRO', ADVANCED_FORM_INTEGRATION_PATH . '/pro' );
            // View Folder Path
        }
        
        /**
         * Include the required files
         *
         * @since 1.0
         * @return void
         */
        public function includes()
        {
            include ADVANCED_FORM_INTEGRATION_INCLUDES . '/class-adfoin-db.php';
            include ADVANCED_FORM_INTEGRATION_INCLUDES . '/class-adfoin-admin-menu.php';
            include ADVANCED_FORM_INTEGRATION_INCLUDES . '/class-adfoin-list-table.php';
            include ADVANCED_FORM_INTEGRATION_INCLUDES . '/class-adfoin-integration.php';
            include ADVANCED_FORM_INTEGRATION_INCLUDES . '/class-adfoin-log.php';
            include ADVANCED_FORM_INTEGRATION_INCLUDES . '/class-adfoin-log-table.php';
            include ADVANCED_FORM_INTEGRATION_INCLUDES . '/class-adfoin-submission.php';
            include ADVANCED_FORM_INTEGRATION_INCLUDES . '/class-adfoin-review.php';
            include ADVANCED_FORM_INTEGRATION_INCLUDES . '/class-oauth.php';
            include ADVANCED_FORM_INTEGRATION_INCLUDES . '/functions-cf7.php';
            include ADVANCED_FORM_INTEGRATION_INCLUDES . '/functions-adfoin.php';
            $platform_settings = adfoin_get_action_platform_settings();
            foreach ( $platform_settings as $platform => $value ) {
                if ( true == $value ) {
                    if ( file_exists( ADVANCED_FORM_INTEGRATION_PLATFORMS . "/{$platform}/{$platform}.php" ) ) {
                        include ADVANCED_FORM_INTEGRATION_PLATFORMS . "/{$platform}/{$platform}.php";
                    }
                }
            }
        }
        
        /**
         * Instantiate classes
         *
         * @since 1.0
         * @return void
         */
        public function init_classes()
        {
            // Admin Menu Class
            new Advanced_Form_Integration_Admin_Menu();
            // Submission Handler Class
            new Advanced_Form_Integration_Submission();
        }
        
        /**
         * Initializes action hooks
         *
         * @since 1.0
         * @return  void
         */
        public function init_actions()
        {
            add_action( 'init', array( $this, 'localization_setup' ) );
            add_action( 'admin_enqueue_scripts', array( $this, 'register_scripts' ) );
            add_action( 'wp_enqueue_scripts', array( $this, 'register_public_scripts' ) );
        }
        
        /**
         * Initialize plugin for localization
         *
         * @since 1.0
         *
         * @uses load_plugin_textdomain()
         *
         * @return void
         */
        public function localization_setup()
        {
            load_plugin_textdomain( 'advanced-form-integration', false, ADVANCED_FORM_INTEGRATION_FILE . '/languages/' );
        }
        
        /**
         * Initializes action filters
         *
         * @since 1.0
         * @return  void
         */
        public function init_filters()
        {
        }
        
        /**
         * Register Script
         *
         * @since 1.0
         * @return mixed | void
         */
        public function register_scripts( $hook )
        {
            wp_register_script(
                'adfoin-vuejs',
                ADVANCED_FORM_INTEGRATION_ASSETS . '/js/vue.min.js',
                array( 'jquery' ),
                '',
                1
            );
            wp_register_script(
                'adfoin-main-script',
                ADVANCED_FORM_INTEGRATION_ASSETS . '/js/script.js',
                array( 'adfoin-vuejs' ),
                $this->version,
                1
            );
            wp_register_style(
                'adfoin-main-style',
                ADVANCED_FORM_INTEGRATION_ASSETS . '/css/asset.css',
                array(),
                $this->version
            );
            $localize_scripts = array(
                'nonce'          => wp_create_nonce( 'advanced-form-integration' ),
                'delete_confirm' => __( 'Are you sure to delete the integration?', 'advanced-form-integration' ),
                'list_url'       => admin_url( 'admin.php?page=advanced-form-integration&status=1' ),
            );
            $localize_scripts['codeEditor'] = wp_enqueue_code_editor( array(
                'type' => 'application/json',
            ) );
            wp_localize_script( 'adfoin-main-script', 'adfoin', $localize_scripts );
        }
        
        /**
         * Register Public Script
         *
         * @since 1.53.0
         * @return mixed | void
         */
        public function register_public_scripts()
        {
            
            if ( 1 == get_option( 'adfoin_general_settings_utm' ) ) {
                wp_enqueue_script(
                    'js.cookie',
                    ADVANCED_FORM_INTEGRATION_ASSETS . '/js/js.cookie.js',
                    array( 'jquery' ),
                    $this->version,
                    1
                );
                wp_enqueue_script(
                    'afi-utm-grabber',
                    ADVANCED_FORM_INTEGRATION_ASSETS . '/js/utm-grabber.js',
                    array( 'jquery', 'js.cookie' ),
                    $this->version,
                    1
                );
            }
        
        }
    
    }
    $adfoin = Advanced_Form_Integration::init();
}
