<?php
/**
 * Above the fold optimization for WordPress
 *
 * This optimization plugin enables above the fold optimization based on the output of a critical path CSS generator and to pass the "Eliminate render-blocking JavaScript and CSS in above-the-fold content" rule from Google PageSpeed to obtain a high score.
 *
 * @link              https://optimalisatie.nl/
 * @since             1.0
 * @package           abovethefold
 *
 * @wordpress-plugin
 * Plugin Name:       Above The Fold Optimization
 * Plugin URI:        https://en.optimalisatie.nl/
 * Description:       Above the fold optimization based on the output of a critical path CSS generator to pass the "<em>Eliminate render-blocking JavaScript and CSS in above-the-fold content</em>" rule from Google PageSpeed.
 * Version:           2.2.1
 * Author:            Optimalisatie.nl
 * Author URI:        https://en.optimalisatie.nl/
 * Text Domain:       abovethefold
 * Domain Path:       /languages
 */

define('WPABOVETHEFOLD_VERSION','2.2.1');

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The core plugin class that is used to define internationalization,
 * admin dashboard hooks and optimization hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/abovethefold.class.php';

/**
 * Begins execution of optimization.
 *
 * The plugin is based on hooks, starting the plugin from here does not impact load speed.
 *
 * @since    1.0
 */
function run_Abovethefold() {
	$GLOBALS['Abovethefold'] = new Abovethefold();
	$GLOBALS['Abovethefold']->run();
}
run_Abovethefold();