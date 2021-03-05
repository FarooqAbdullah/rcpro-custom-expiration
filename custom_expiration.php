<?php
/**
 * Plugin Name: RCPro Custom Expiration
 * Plugin URI: ldninjas.com
 * Description: This plugin is designed to customize Membership expiry date.
 * Version: 1.0
 * Author: ldninjas.com
 * Author URI: ldninjas.com
 * Text Domain: rcpro-expiration-add-on
 */

if( ! defined( 'ABSPATH' ) ) exit;

class Rcpro_Custom_Expiration {

    const VERSION = '1.0';

    /**
     * @var self
     */
    private static $instance = null;

    /**
     * @since 1.0
     * @return $this
     */
    public static function instance() {
        if ( is_null( self::$instance ) && ! ( self::$instance instanceof Rcpro_Custom_Expiration ) ) {
            self::$instance = new self;
            self::$instance->setup_constants();
            self::$instance->hooks();
        }
        return self::$instance;
    }

    public  function setup_constants() {

        /**
         * Directory
         */
        define( 'RCPRO_DIR', plugin_dir_path ( __FILE__ ) );
        define( 'RCPRO_DIR_FILE', RCPRO_DIR . basename ( __FILE__ ) );
        define( 'RCPRO_INCLUDES_DIR', trailingslashit ( RCPRO_DIR . 'includes' ) );
        define( 'RCPRO_TEMPLATES_DIR', trailingslashit ( RCPRO_DIR . 'templates' ) );
        define( 'RCPRO_BASE_DIR', plugin_basename(__FILE__));

        /**
         * URLs
         */
        define( 'RCPRO_URL', trailingslashit ( plugins_url ( '', __FILE__ ) ) );
        define( 'RCPRO_ASSETS_URL', trailingslashit ( RCPRO_URL . 'assets' ) );

        /**
         * Text Domain
         */
        define( 'RCPRO_TEXT_DOMAIN', 'Rcpro-expiration-add-on' );
    }

    public  function hooks() {
        add_filter( 'rcp_membership_get_expiration_date', [ $this, 'override_expiration_date' ]);
        add_filter( 'rcp_calculate_membership_level_expiration', [ $this, 'set_expiration_date' ]);
        add_filter( 'rcp_membership_calculated_expiration_date', [ $this, 'set_renew_date' ]);
    }

    /**
     * Update expiration on purchase
     *
     * @param $expiration
     * @return false|string
     */
    public function override_expiration_date( $expiration ) {

        if( is_admin() ) {
            $screen = get_current_screen();
            if( $screen->parent_base == 'rcp-members' || ( isset( $_GET['page'] ) && $_GET['page'] == 'rcp-members' ) ) {
                return $expiration;
            }
        }

        if ( 'none' != $expiration ) {
            date_default_timezone_set('GMT');
            $expiration = date( 'Y-m-d 23:59:59', strtotime( date( 'Y-m-t' ), strtotime( '+8 hours' ) ) );
        }
        
        return $expiration;
    }

    /**
     * Update expiration on purchase
     *
     * @param $expiration
     * @return false|string
     */
    public function set_expiration_date( $expiration ) {
        if ( 'none' != $expiration ) {
            date_default_timezone_set('GMT');
            $expiration = date( 'Y-m-d 23:59:59', strtotime( date( 'Y-m-t' ), strtotime( '+8 hours' ) ) );
        }
        return $expiration;
    }

    /**
     * Update expiratio on renewal
     *
     * @param $expiration
     * @return false|string
     */
    public function set_renew_date( $expiration ){
    if ( 'none' != $expiration ) {
        date_default_timezone_set('GMT');
        $expiration = date( 'Y-m-d 23:59:59', strtotime( date( 'Y-m-t' ), strtotime( '+8 hours' ) ) );
        }
        return $expiration;

    }
}

/**
 * Display admin notifications if dependency not found.
 */
function rcpro_ready() {
    if( !is_admin() ) {
        return;
    }

    if( !class_exists( 'RCP_Requirements_Check' ) ) {
        deactivate_plugins ( plugin_basename ( __FILE__ ), true );
        $class = 'notice is-dismissible error';
        $message = __( ' RCPro Custom Expiration add-on requires Restrict Content Pro to be activated', 'rcpro-expiration-add-on' );
        printf ( '<div id="message" class="%s"> <p>%s</p></div>', $class, $message );
    }
}

/**
 * @return bool
 */
function RCPRO() {

    if ( ! class_exists( 'RCP_Requirements_Check' ) ) {
        add_action( 'admin_notices', 'rcpro_ready' );
        return false;
    }

    return Rcpro_Custom_Expiration::instance();
}
add_action( 'plugins_loaded', 'RCPRO' );