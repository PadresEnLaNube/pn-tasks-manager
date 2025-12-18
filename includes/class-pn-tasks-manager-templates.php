<?php
/**
 * Load the plugin templates.
 *
 * Loads the plugin template files getting them from the templates folders inside common, public or admin, depending on access requirements.
 *
 * @link       padresenlanube.com/
 * @since      1.0.0
 * @package    PN_TASKS_MANAGER
 * @subpackage pn-tasks-manager/includes
 * @author     Padres en la Nube
 */
class PN_TASKS_MANAGER_Templates {
	/**
	 * Load the plugin templates.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_templates() {
		require_once PN_TASKS_MANAGER_DIR . 'templates/pn-tasks-manager-footer.php';
		require_once PN_TASKS_MANAGER_DIR . 'templates/pn-tasks-manager-popups.php';
	}
}