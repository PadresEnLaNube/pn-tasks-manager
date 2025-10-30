<?php

/**
 * Fired during plugin deactivation
 *
 * @link       padresenlanube.com/
 * @since      1.0.0
 *
 * @package    TASKSPN
 * @subpackage TASKSPN/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    TASKSPN
 * @subpackage TASKSPN/includes
 * @author     Padres en la Nube
 */
class TASKSPN_Deactivator {

	/**
	 * Plugin deactivation functions
	 *
	 * Functions to be loaded on plugin deactivation. This actions remove roles, options and post information attached to the plugin.
	 *
	 * @since    1.0.0
	 */
	public static function taskspn_deactivate() {
		$plugin_post = new TASKSPN_Post_Type_Task();
		
		if (get_option('taskspn_options_remove') == 'on') {
      remove_role('taskspn_role_manager');

      $taskspn_task = get_posts(['fields' => 'ids', 'numberposts' => -1, 'post_type' => 'taskspn_task', 'post_status' => 'any', ]);

      if (!empty($taskspn_task)) {
        foreach ($taskspn_task as $post_id) {
          wp_delete_post($post_id, true);
        }
      }

      foreach ($plugin_post->taskspn_get_fields() as $taskspn_option) {
        delete_option($taskspn_option['id']);
      }
    }

    update_option('taskspn_options_changed', true);
	}
}