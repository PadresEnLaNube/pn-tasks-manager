<?php
/**
 * Task taxonomies creator.
 *
 * This class defines Task taxonomies.
 *
 * @link       padresenlanube.com/
 * @since      1.0.0
 * @package    PN_TASKS_MANAGER
 * @subpackage pn-tasks-manager/includes
 * @author     Padres en la Nube
 */
class PN_TASKS_MANAGER_Taxonomies_Task { 
	/**
	 * Register taxonomies.
	 *
	 * @since    1.0.0
	 */
	public static function pn_tasks_manager_register_taxonomies() {
		$taxonomies = [
			'pn_tasks_manager_task_category' => [
				'name'              		=> _x('Task category', 'Taxonomy general name', 'pn-tasks-manager'),
				'singular_name'     		=> _x('Task category', 'Taxonomy singular name', 'pn-tasks-manager'),
				'search_items'     			=> esc_html(__('Search Task categories', 'pn-tasks-manager')),
	        'all_items'         			=> esc_html(__('All Task categories', 'pn-tasks-manager')),
	        'parent_item'       			=> esc_html(__('Parent Task category', 'pn-tasks-manager')),
	        'parent_item_colon' 			=> esc_html(__('Parent Task category:', 'pn-tasks-manager')),
	        'edit_item'         			=> esc_html(__('Edit Task category', 'pn-tasks-manager')),
	        'update_item'       			=> esc_html(__('Update Task category', 'pn-tasks-manager')),
	        'add_new_item'      			=> esc_html(__('Add New Task category', 'pn-tasks-manager')),
	        'new_item_name'     			=> esc_html(__('New Task category', 'pn-tasks-manager')),
	        'menu_name'         			=> esc_html(__('Task categories', 'pn-tasks-manager')),
			],
		];

	  foreach ($taxonomies as $taxonomy => $options) {
	  	$labels = [
				'name'          			=> $options['name'],
				'singular_name' 			=> $options['singular_name'],
			];

			$args = [
				'labels'            		=> $labels,
				'hierarchical'      		=> true,
				'public'            		=> false,
				'publicly_queryable'		=> false,
				'show_ui' 					=> true,
				'show_in_nav_menus' 		=> false,
				'query_var'         		=> false,
				'rewrite'           		=> false,
				'show_in_rest'      		=> true,
				// Use dynamically defined capabilities constant for pn_tasks_task CPT
				'capabilities'      		=> defined('PN_TASKS_MANAGER_ROLE_PN_TASKS_TASK_CAPABILITIES') ? constant('PN_TASKS_MANAGER_ROLE_PN_TASKS_TASK_CAPABILITIES') : [],
			];

			register_taxonomy($taxonomy, 'pn_tasks_task', $args);
			register_taxonomy_for_object_type($taxonomy, 'pn_tasks_task');
		}
	}
}