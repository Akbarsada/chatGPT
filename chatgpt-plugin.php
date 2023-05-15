<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://smartcoding.at
 * @since             1.0.0
 * @package           Chatgpt_Connect
 *
 * @wordpress-plugin
 * Plugin Name:       ChatGPT Connect
 * Description:       ChatGPT, Content Writer, Auto Content Writer, ChatBot, Image Generator, Audio Converter, WooCommerce Product Writer, SEO optimizer, AI Training, Embeddings, Title Suggestions and hundreds of ready to use prompts and forms.
 * Version:           1.0.0
 * Author:            smartcoding
 * Author URI:        https://smartcoding.at
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       chatgpt_connect
 * Domain Path:       /languages
 */
if ( !defined( 'WPINC' ) ) {
    die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'CHATGPT_CONNECT_VERSION', '1.0.0' );
if ( !class_exists( '\\CHATGPT_CONNECT\\CHATGPT_OPEN_AI' ) ) {
    require_once __DIR__ . '/includes/CHATGPT_OPEN_URL.php';
    require_once __DIR__ . '/includes/CHATGPT_OPEN_AI.php';
}
/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-wp-ai-content-generator-activator.php
 */
function activate_chatgpt_conn()
{
    require_once plugin_dir_path( __FILE__ ) . 'includes/Chatgpt_Activate.php';
    Chatgpt_Activate::activate();
}
function deactivate_chatgpt_conn()
{
    require_once plugin_dir_path( __FILE__ ) . 'includes/chatgpt_deactivate.php';
    chatgpt_deactivate::deactivate();
}
function uninstall_chatgpt_conn()
{
    require_once plugin_dir_path( __FILE__ ) . 'includes/chatgpt_uninstall.php';
    chatgpt_unistall::uninstall();
}

register_activation_hook( __FILE__, 'activate_chatgpt_conn' );
register_deactivation_hook( __FILE__, 'deactivate_chatgpt_conn' );
register_uninstall_hook( __FILE__, 'uninstall_chatgpt_conn' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/Chatgpt_Define.php';

function run_generator()
{
    $plugin = new Chatgpt_Define();
    $plugin->run();
}



run_generator();

require_once __DIR__ . '/chatgpt_class_loader.php';


