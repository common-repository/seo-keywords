<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://wpseoplugins.org/
 * @since             1.0.0
 * @package           Seo_Keywords
 *
 * @wordpress-plugin
 * Plugin Name:       Seo Keywords
 * Plugin URI:        https://wpseoplugins.org/seo-keywords/
 * Description:       SEO Keywords is a powerful plugin that helps you add keywords in your wordpress posts. Automate keywords building with ease!
 * Version:           1.0.9
 * Author:            WP SEO Plugins
 * Author URI:        https://wpseoplugins.org/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       seo-keywords
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

#Define plugin constant.
define( 'SEO_KEYWORDS_PLUGIN_FOLDER', dirname(__FILE__) );
define( 'SEO_KEYWORDS_CORE_FOLDER', SEO_KEYWORDS_PLUGIN_FOLDER.'/seo-keywords');
if( !defined( 'WP_SEO_PLUGINS_BACKEND_URL' ) ) {
    define( 'WP_SEO_PLUGINS_BACKEND_URL', 'https://api.wpseoplugins.org/');
}
define( 'SEO_KEYWORDS_LICENSE', true );
define( 'SEO_KEYWORDS_SERVER_NAME', sanitize_text_field( $_SERVER['SERVER_NAME'] ));
define( 'SEO_KEYWORDS_SERVER_PORT', sanitize_text_field( $_SERVER['SERVER_PORT'] ));
define( 'SEO_KEYWORDS_SITE_URL', ( SEO_KEYWORDS_SERVER_PORT == 80 ? 'http://' : 'https://' ) . SEO_KEYWORDS_SERVER_NAME );
define( 'SEO_KEYWORDS_SERVER_REQUEST_URI', sanitize_text_field( $_SERVER['REQUEST_URI'] ) );
define( 'SEO_KEYWORDS_VERSION', '1.0.9' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-seo-keywords-activator.php
 */
function activate_seo_keywords() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-seo-keywords-activator.php';
	Seo_Keywords_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-seo-keywords-deactivator.php
 */
function deactivate_seo_keywords() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-seo-keywords-deactivator.php';
	Seo_Keywords_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_seo_keywords' );
register_deactivation_hook( __FILE__, 'deactivate_seo_keywords' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-seo-keywords.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_seo_keywords() {

	$plugin = new Seo_Keywords();
	$plugin->run();

}
run_seo_keywords();
