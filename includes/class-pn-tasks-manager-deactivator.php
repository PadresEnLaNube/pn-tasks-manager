<?php

/**
 * Fired during plugin deactivation
 *
 * @link       padresenlanube.com/
 * @since      1.0.0
 *
 * @package    PN_TASKS_MANAGER
 * @subpackage pn-tasks-manager/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    PN_TASKS_MANAGER
 * @subpackage pn-tasks-manager/includes
 * @author     Padres en la Nube
 */
class PN_TASKS_MANAGER_Deactivator {

	/**
	 * Plugin deactivation functions
	 *
	 * Functions to be loaded on plugin deactivation. This actions remove roles, options and post information attached to the plugin.
	 *
	 * @since    1.0.0
	 */
	public static function pn_tasks_manager_deactivate() {
		// Clear scheduled cron job
		$timestamp = wp_next_scheduled('pn_tasks_manager_reset_repeated_tasks');
		if ($timestamp) {
			wp_unschedule_event($timestamp, 'pn_tasks_manager_reset_repeated_tasks');
		}
		
		$plugin_post = new PN_TASKS_MANAGER_Post_Type_Task();
		
		if (get_option('pn_tasks_manager_options_remove') == 'on') {
      remove_role('pn_tasks_manager_role_manager');

      $pn_tasks_manager_task = get_posts(['fields' => 'ids', 'numberposts' => -1, 'post_type' => 'pn_tasks_task', 'post_status' => 'any', ]);

      if (!empty($pn_tasks_manager_task)) {
        foreach ($pn_tasks_manager_task as $post_id) {
          wp_delete_post($post_id, true);
        }
      }

      foreach ($plugin_post->pn_tasks_manager_get_fields() as $pn_tasks_manager_option) {
        delete_option($pn_tasks_manager_option['id']);
      }
    }

    update_option('pn_tasks_manager_options_changed', true);
	}
}