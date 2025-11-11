<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin admin area. This file also includes all of the dependencies used by the plugin, registers the activation and deactivation functions, and defines a function that starts the plugin.
 *
 * @link              padresenlanube.com/
 * @since             1.0.0
 * @package           TASKSPN
 *
 * @wordpress-plugin
 * Plugin Name:       Tasks Manager - PN
 * Plugin URI:        https://padresenlanube.com/plugins/taskspn/
 * Description:       Manage your tasks and time tracking with this plugin. Create tasks, assign them to users, and track the time spent on each task.
 * Version:           1.0.11
 * Requires at least: 3.0
 * Requires PHP:      7.2
 * Author:            Padres en la Nube
 * Author URI:        https://padresenlanube.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       taskspn
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define('TASKSPN_VERSION', '1.0.11');
define('TASKSPN_DIR', plugin_dir_path(__FILE__));
define('TASKSPN_URL', plugin_dir_url(__FILE__));
define('TASKSPN_CPTS', [
	'taskspn_task' => 'Task',
]);

/**
 * Plugin role capabilities
 */
$taskspn_role_cpt_capabilities = [];

foreach (TASKSPN_CPTS as $taskspn_cpt_key => $taskspn_cpt_value) {
	$taskspn_role_cpt_capabilities[$taskspn_cpt_key] = [
		'edit_post' 				=> 'edit_' . $taskspn_cpt_key,
		'edit_posts' 				=> 'edit_' . $taskspn_cpt_key,
		'edit_private_posts' 		=> 'edit_private_' . $taskspn_cpt_key,
		'edit_published_posts' 		=> 'edit_published_' . $taskspn_cpt_key,
		'edit_others_posts' 		=> 'edit_others_' . $taskspn_cpt_key,
		'publish_posts' 			=> 'publish_' . $taskspn_cpt_key,

		// Post reading capabilities
		'read_post' 				=> 'read_' . $taskspn_cpt_key,
		'read_private_posts' 		=> 'read_private_' . $taskspn_cpt_key,
		
		// Post deletion capabilities
		'delete_post' 				=> 'delete_' . $taskspn_cpt_key,
		'delete_posts' 				=> 'delete_' . $taskspn_cpt_key,
		'delete_private_posts' 		=> 'delete_private_' . $taskspn_cpt_key,
		'delete_published_posts' 	=> 'delete_published_' . $taskspn_cpt_key,
		'delete_others_posts'		=> 'delete_others_' . $taskspn_cpt_key,

		// Media capabilities
		'upload_files' 				=> 'upload_files',

		// Taxonomy capabilities
		'manage_terms' 				=> 'manage_' . $taskspn_cpt_key . '_category',
		'edit_terms' 				=> 'edit_' . $taskspn_cpt_key . '_category',
		'delete_terms' 				=> 'delete_' . $taskspn_cpt_key . '_category',
		'assign_terms' 				=> 'assign_' . $taskspn_cpt_key . '_category',

		// Options capabilities
		'manage_options' 			=> 'manage_' . $taskspn_cpt_key . '_options'
	];
	
	define('TASKSPN_ROLE_' . strtoupper($taskspn_cpt_key) . '_CAPABILITIES', $taskspn_role_cpt_capabilities[$taskspn_cpt_key]);
}

/**
 * Plugin KSES allowed HTML elements and attributes
 */
$taskspn_kses = [
	// Basic text elements
	'div' => ['id' => [], 'class' => [], 'style' => []],
	'section' => ['id' => [], 'class' => []],
	'article' => ['id' => [], 'class' => []],
	'aside' => ['id' => [], 'class' => []],
	'footer' => ['id' => [], 'class' => []],
	'header' => ['id' => [], 'class' => []],
	'main' => ['id' => [], 'class' => []],
	'nav' => ['id' => [], 'class' => []],
	'p' => ['id' => [], 'class' => []],
	'span' => ['id' => [], 'class' => [], 'style' => []],
	'small' => ['id' => [], 'class' => []],
	'em' => [],
	'strong' => [],
	'br' => [],

	// Headings
	'h1' => ['id' => [], 'class' => []],
	'h2' => ['id' => [], 'class' => []],
	'h3' => ['id' => [], 'class' => []],
	'h4' => ['id' => [], 'class' => []],
	'h5' => ['id' => [], 'class' => []],
	'h6' => ['id' => [], 'class' => []],

	// Lists
	'ul' => ['id' => [], 'class' => []],
	'ol' => ['id' => [], 'class' => []],
	'li' => [
		'id' => [],
		'class' => [],
	],

	// Links and media
	'a' => [
		'id' => [],
		'class' => [],
		'href' => [],
		'title' => [],
		'target' => [],
		'data-taskspn-ajax-type' => [],
		'data-taskspn-popup-id' => [],
	],
	'img' => [
		'id' => [],
		'class' => [],
		'src' => [],
		'alt' => [],
		'title' => [],
	],
	'i' => [
		'id' => [], 
		'class' => [], 
		'title' => []
	],

	// Forms and inputs
	'form' => [
		'id' => [],
		'class' => [],
		'action' => [],
		'method' => [],
	],
	'input' => [
		'name' => [],
		'id' => [],
		'class' => [],
		'type' => [],
		'checked' => [],
		'multiple' => [],
		'disabled' => [],
		'value' => [],
		'placeholder' => [],
		'data-taskspn-parent' => [],
		'data-taskspn-parent-option' => [],
		'data-taskspn-type' => [],
		'data-taskspn-subtype' => [],
		'data-taskspn-user-id' => [],
		'data-taskspn-post-id' => [],
	],
	'select' => [
		'name' => [],
		'id' => [],
		'class' => [],
		'type' => [],
		'checked' => [],
		'multiple' => [],
		'disabled' => [],
		'value' => [],
		'placeholder' => [],
		'data-placeholder' => [],
		'data-taskspn-parent' => [],
		'data-taskspn-parent-option' => [],
	],
	'option' => [
		'name' => [],
		'id' => [],
		'class' => [],
		'disabled' => [],
		'selected' => [],
		'value' => [],
		'placeholder' => [],
	],
	'textarea' => [
		'name' => [],
		'id' => [],
		'class' => [],
		'type' => [],
		'multiple' => [],
		'disabled' => [],
		'value' => [],
		'placeholder' => [],
		'data-taskspn-parent' => [],
		'data-taskspn-parent-option' => [],
	],
	'label' => [
		'id' => [],
		'class' => [],
		'for' => [],
	],
];

foreach (TASKSPN_CPTS as $taskspn_cpt_key => $taskspn_cpt_value) {
	$taskspn_kses['li']['data-' . $taskspn_cpt_key . '-id'] = [];
}

// Now define the constant with the complete array
define('TASKSPN_KSES', $taskspn_kses);

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-taskspn-activator.php
 */
function taskspn_activation_hook() {
	require_once plugin_dir_path(__FILE__) . 'includes/class-taskspn-activator.php';
	TASKSPN_Activator::taskspn_activate();
	
	// Clear any previous state
	delete_option('taskspn_redirecting');
	
	// Set transient only if it doesn't exist
	if (!get_transient('taskspn_just_activated')) {
		set_transient('taskspn_just_activated', true, 30);
	}
}

// Register activation hook
register_activation_hook(__FILE__, 'taskspn_activation_hook');

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-taskspn-deactivator.php
 */
function taskspn_deactivation_cleanup() {
	delete_option('taskspn_redirecting');
}
register_deactivation_hook(__FILE__, 'taskspn_deactivation_cleanup');

/**
 * The core plugin class that is used to define internationalization, admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-taskspn.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks, then kicking off the plugin from this point in the file does not affect the page life cycle.
 *
 * @since    1.0.0
 */
function taskspn_run() {
	$plugin = new TASKSPN();
	$plugin->taskspn_run();
}

// Initialize the plugin on init hook instead of plugins_loaded
add_action('init', 'taskspn_run', 0);