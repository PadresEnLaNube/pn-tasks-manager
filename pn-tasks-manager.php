<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin admin area. This file also includes all of the dependencies used by the plugin, registers the activation and deactivation functions, and defines a function that starts the plugin.
 *
 * @link              padresenlanube.com/
 * @since             1.0.0
 * @package           PN_TASKS_MANAGER
 *
 * @wordpress-plugin
 * Plugin Name:       PN Tasks Manager
 * Plugin URI:        https://padresenlanube.com/plugins/pn-tasks-manager/
 * Description:       Manage your tasks and time tracking with this plugin. Create tasks, assign them to users, and track the time spent on each task. Features include: interactive calendar views (day, week, month, year), recurring tasks with customizable periodicity, task categories for organization, public tasks that users can join, ICS calendar export, user ranking by completed hours, Gutenberg blocks integration, email notifications for task assignments, shortcodes for displaying calendars and task lists, multilingual support, and flexible role-based permissions.
 * Version:           1.0.10
 * Requires at least: 3.0
 * Requires PHP:      7.2
 * Author:            Padres en la Nube
 * Author URI:        https://padresenlanube.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       pn-tasks-manager
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
define('PN_TASKS_MANAGER_VERSION', '1.0.10');
define('PN_TASKS_MANAGER_DIR', plugin_dir_path(__FILE__));
define('PN_TASKS_MANAGER_URL', plugin_dir_url(__FILE__));
define('PN_TASKS_MANAGER_CPTS', [
	'pn_tasks_task' => 'Task',
]);

/**
 * Plugin role capabilities
 */
$pn_tasks_manager_role_cpt_capabilities = [];

foreach (PN_TASKS_MANAGER_CPTS as $pn_tasks_manager_cpt_key => $pn_tasks_manager_cpt_value) {
	$pn_tasks_manager_role_cpt_capabilities[$pn_tasks_manager_cpt_key] = [
		'edit_post' 				=> 'edit_' . $pn_tasks_manager_cpt_key,
		'edit_posts' 				=> 'edit_' . $pn_tasks_manager_cpt_key,
		'edit_private_posts' 		=> 'edit_private_' . $pn_tasks_manager_cpt_key,
		'edit_published_posts' 		=> 'edit_published_' . $pn_tasks_manager_cpt_key,
		'edit_others_posts' 		=> 'edit_others_' . $pn_tasks_manager_cpt_key,
		'publish_posts' 			=> 'publish_' . $pn_tasks_manager_cpt_key,

		// Post reading capabilities
		'read_post' 				=> 'read_' . $pn_tasks_manager_cpt_key,
		'read_private_posts' 		=> 'read_private_' . $pn_tasks_manager_cpt_key,
		
		// Post deletion capabilities
		'delete_post' 				=> 'delete_' . $pn_tasks_manager_cpt_key,
		'delete_posts' 				=> 'delete_' . $pn_tasks_manager_cpt_key,
		'delete_private_posts' 		=> 'delete_private_' . $pn_tasks_manager_cpt_key,
		'delete_published_posts' 	=> 'delete_published_' . $pn_tasks_manager_cpt_key,
		'delete_others_posts'		=> 'delete_others_' . $pn_tasks_manager_cpt_key,

		// Media capabilities
		'upload_files' 				=> 'upload_files',

		// Taxonomy capabilities
		'manage_terms' 				=> 'manage_' . $pn_tasks_manager_cpt_key . '_category',
		'edit_terms' 				=> 'edit_' . $pn_tasks_manager_cpt_key . '_category',
		'delete_terms' 				=> 'delete_' . $pn_tasks_manager_cpt_key . '_category',
		'assign_terms' 				=> 'assign_' . $pn_tasks_manager_cpt_key . '_category',

		// Options capabilities
		'manage_options' 			=> 'manage_' . $pn_tasks_manager_cpt_key . '_options'
	];
	
	define('PN_TASKS_MANAGER_ROLE_' . strtoupper($pn_tasks_manager_cpt_key) . '_CAPABILITIES', $pn_tasks_manager_role_cpt_capabilities[$pn_tasks_manager_cpt_key]);
}

/**
 * Plugin KSES allowed HTML elements and attributes
 */
$pn_tasks_manager_kses = [
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
	'small' => ['id' => [], 'class' => [], 'style' => []],
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
		'data-pn_tasks_manager_task-id' => [],
	],

	// Links and media
	'a' => [
		'id' => [],
		'class' => [],
		'href' => [],
		'title' => [],
		'target' => [],
		'data-pn-tasks-manager-ajax-type' => [],
		'data-pn-tasks-manager-popup-id' => [],
		'data-pn_tasks_manager_task-id' => [],
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
		'title' => [],
		'style' => [],
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
		'data-pn-tasks-manager-parent' => [],
		'data-pn-tasks-manager-parent-option' => [],
		'data-pn-tasks-manager-type' => [],
		'data-pn-tasks-manager-subtype' => [],
		'data-pn-tasks-manager-user-id' => [],
		'data-pn-tasks-manager-post-id' => [],
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
		'data-pn-tasks-manager-parent' => [],
		'data-pn-tasks-manager-parent-option' => [],
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
		'data-pn-tasks-manager-parent' => [],
		'data-pn-tasks-manager-parent-option' => [],
	],
	'label' => [
		'id' => [],
		'class' => [],
		'for' => [],
	],
];

foreach (PN_TASKS_MANAGER_CPTS as $pn_tasks_manager_cpt_key => $pn_tasks_manager_cpt_value) {
	$pn_tasks_manager_kses['li']['data-' . $pn_tasks_manager_cpt_key . '-id'] = [];
}

// Now define the constant with the complete array
define('PN_TASKS_MANAGER_KSES', $pn_tasks_manager_kses);

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-pn-tasks-manager-activator.php
 */
function pn_tasks_manager_activation_hook() {
	require_once plugin_dir_path(__FILE__) . 'includes/class-pn-tasks-manager-activator.php';
	PN_TASKS_MANAGER_Activator::pn_tasks_manager_activate();
	
	// Clear any previous state
	delete_option('pn_tasks_manager_redirecting');
	
	// Set transient only if it doesn't exist
	if (!get_transient('pn_tasks_manager_just_activated')) {
		set_transient('pn_tasks_manager_just_activated', true, 30);
	}
}

// Register activation hook
register_activation_hook(__FILE__, 'pn_tasks_manager_activation_hook');

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-pn-tasks-manager-deactivator.php
 */
function pn_tasks_manager_deactivation_cleanup() {
	delete_option('pn_tasks_manager_redirecting');
}
register_deactivation_hook(__FILE__, 'pn_tasks_manager_deactivation_cleanup');

/**
 * The core plugin class that is used to define internationalization, admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-pn-tasks-manager.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks, then kicking off the plugin from this point in the file does not affect the page life cycle.
 *
 * @since    1.0.0
 */
function pn_tasks_manager_run() {
	$plugin = new PN_TASKS_MANAGER();
	$plugin->pn_tasks_manager_run();
}

// Initialize the plugin on init hook instead of plugins_loaded
add_action('init', 'pn_tasks_manager_run', 0);