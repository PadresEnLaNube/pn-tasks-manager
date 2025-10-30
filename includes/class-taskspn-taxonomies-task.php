<?php
/**
 * Task taxonomies creator.
 *
 * This class defines Task taxonomies.
 *
 * @link       padresenlanube.com/
 * @since      1.0.0
 * @package    TASKSPN
 * @subpackage TASKSPN/includes
 * @author     Padres en la Nube
 */
class TASKSPN_Taxonomies_Task { 
	/**
	 * Register taxonomies.
	 *
	 * @since    1.0.0
	 */
	public static function taskspn_register_taxonomies() {
		$taxonomies = [
			'taskspn_task_category' => [
				'name'              		=> _x('Task category', 'Taxonomy general name', 'taskspn'),
				'singular_name'     		=> _x('Task category', 'Taxonomy singular name', 'taskspn'),
				'search_items'     			=> esc_html(__('Search Task categories', 'taskspn')),
	        'all_items'         			=> esc_html(__('All Task categories', 'taskspn')),
	        'parent_item'       			=> esc_html(__('Parent Task category', 'taskspn')),
	        'parent_item_colon' 			=> esc_html(__('Parent Task category:', 'taskspn')),
	        'edit_item'         			=> esc_html(__('Edit Task category', 'taskspn')),
	        'update_item'       			=> esc_html(__('Update Task category', 'taskspn')),
	        'add_new_item'      			=> esc_html(__('Add New Task category', 'taskspn')),
	        'new_item_name'     			=> esc_html(__('New Task category', 'taskspn')),
	        'menu_name'         			=> esc_html(__('Task categories', 'taskspn')),
				'archive'			      	=> true,
				'slug'			      		=> 'task-category',
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
				'public'            		=> true,
				'show_ui' 					=> true,
				'query_var'         		=> true,
				'rewrite'           		=> true,
				'show_in_rest'      		=> true,
				'capabilities'      		=> constant('TASKSPN_ROLE_TASKSPN_TASK_CAPABILITIES'),
			];

			if ($options['archive']) {
				$args['public'] = true;
				$args['publicly_queryable'] = true;
				$args['show_in_nav_menus'] = true;
				$args['query_var'] = $taxonomy;
				$args['show_ui'] = true;
				$args['rewrite'] = [
					'slug' 					=> $options['slug'],
				];
			}

			register_taxonomy($taxonomy, 'taskspn_task', $args);
			register_taxonomy_for_object_type($taxonomy, 'taskspn_task');
		}
	}
}