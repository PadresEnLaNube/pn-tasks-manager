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
		if (!is_admin() && !$this->pn_tasks_manager_should_load_templates()) {
			return;
		}

		require_once PN_TASKS_MANAGER_DIR . 'templates/pn-tasks-manager-footer.php';
		require_once PN_TASKS_MANAGER_DIR . 'templates/pn-tasks-manager-popups.php';
	}

	/**
	 * Check if templates should be loaded on the front-end.
	 *
	 * @since    1.0.6
	 * @return   bool
	 */
	private function pn_tasks_manager_should_load_templates() {
		if (is_singular('pn_tasks_task') || is_post_type_archive('pn_tasks_task')) {
			return true;
		}

		if (is_singular()) {
			$post = get_post();
			if ($post) {
				$content = $post->post_content;
				$shortcodes = ['pn-tasks-manager-joinable-tasks','pn-tasks-manager-users-ranking','pn-tasks-manager-calendar','pn-tasks-manager-task','pn-tasks-manager-task-list'];
				foreach ($shortcodes as $sc) {
					if (has_shortcode($content, $sc)) { return true; }
				}
				if (function_exists('has_block') && (
					has_block('pn-tasks-manager/joinable-tasks', $post) ||
					has_block('pn-tasks-manager/users-ranking', $post) ||
					has_block('pn-tasks-manager/calendar', $post) ||
					has_block('pn-tasks-manager/task', $post) ||
					has_block('pn-tasks-manager/task-list', $post)
				)) {
					return true;
				}
			}
		}

		return false;
	}
}