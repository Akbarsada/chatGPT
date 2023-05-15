<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Wp_Ai_Content_Generator
 * @subpackage Wp_Ai_Content_Generator/includes
 * @author     Senol Sahin <senols@gmail.com>
 */

class Chatgpt_Languages {


    /**
     * Load the plugin text domain for translation.
     *
     * @since    1.0.0
     */
    public function load_plugin_textdomain() {

        load_plugin_textdomain(
            'chatgpt_connect',
            false,
            dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
        );

    }



}