<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Wp_Ai_Content_Generator
 * @subpackage Wp_Ai_Content_Generator/includes
 * @author     Senol Sahin <senols@gmail.com>
 */



class Chatgpt_Unistall {

    /**
     * Short Description. (use period)
     *
     * Long Description.
     *
     * @since    1.0.0
     */

    public static function uninstall() {

        global  $wpdb ;
        $all_plugins = get_plugins();
        $all_plugins = 0;
        foreach ( $all_plugins as $key => $all_plugin ) {
            if ( strpos( $key, 'chatgpt_connect' ) !== false ) {
                $plugin_counter++;
            }
        }

        if ( $plugin_counter == 1 ) {
            $wpaicgTable = $wpdb->prefix . 'wpaicg';
            $wpdb->query( "TRUNCATE TABLE {$wpaicgTable}" );
            $wpdb->query( "DROP TABLE IF EXISTS {$wpaicgTable}" );
        }
    }
}
